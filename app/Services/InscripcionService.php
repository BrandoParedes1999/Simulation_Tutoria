<?php

namespace App\Services;

use App\Exceptions\InscripcionException;
use App\Models\Alumno;
use App\Models\Inscripcion;
use App\Models\MateriaMalla;
use App\Models\Periodo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InscripcionService
{
    /**
     * Inscribe al alumno en una o varias materias (transaccional).
     * Si cualquiera falla, ninguna se guarda.
     *
     * @param  Alumno  $alumno
     * @param  array<int>  $materiasIds
     * @return Collection<Inscripcion>
     * @throws InscripcionException
     */
    public function inscribir(Alumno $alumno, array $materiasIds): Collection
    {
        $this->validarAlumno($alumno);
        $periodo = $this->obtenerPeriodoActivo();
        $this->validarPeriodoAbierto($periodo);

        $materias = MateriaMalla::whereIn('id', $materiasIds)
            ->where('activa', true)
            ->get();

        if ($materias->count() !== count($materiasIds)) {
            throw new InscripcionException('Alguna de las materias seleccionadas no existe o no está activa.');
        }

        // Pre-calcular inscripciones previas del alumno
        $inscripcionesPrevias = $alumno->inscripciones()
            ->select('materia_malla_id', 'estatus', 'periodo_id')
            ->get();

        $idsAprobadas = $inscripcionesPrevias
            ->where('estatus', 'aprobada')
            ->pluck('materia_malla_id')
            ->flip()
            ->toArray();

        $idsActivas = $inscripcionesPrevias
            ->where('periodo_id', $periodo->id)
            ->whereIn('estatus', ['en_curso', 'aprobada'])
            ->pluck('materia_malla_id')
            ->flip()
            ->toArray();

        // Validar cada materia antes de crear nada
        foreach ($materias as $materia) {
            $this->validarMateriaParaInscribir(
                $materia,
                $idsAprobadas,
                $idsActivas
            );
        }

        // Transacción: todas las inscripciones se crean o ninguna
        return DB::transaction(function () use ($alumno, $materias, $periodo) {
            $creadas = collect();

            foreach ($materias as $materia) {
                $inscripcion = Inscripcion::create([
                    'alumno_id' => $alumno->id,
                    'materia_malla_id' => $materia->id,
                    'periodo_id' => $periodo->id,
                    'estatus' => 'en_curso',
                ]);
                $creadas->push($inscripcion);
            }

            // Limpiar cache de la malla (ya cambió el estado)
            Cache::forget("malla_carrera_{$alumno->carrera_id}");

            return $creadas;
        });
    }

    /**
     * Da de baja una inscripción si aún está dentro del periodo permitido.
     */
    public function darDeBaja(Inscripcion $inscripcion, Alumno $alumno): bool
    {
        if ($inscripcion->alumno_id !== $alumno->id) {
            throw InscripcionException::noEsTuInscripcion();
        }

        if ($inscripcion->estatus !== 'en_curso') {
            throw new InscripcionException('Solo puedes dar de baja materias en curso.');
        }

        $periodo = $inscripcion->periodo;

        if (!$periodo->estaAbiertoParaBaja()) {
            throw InscripcionException::periodoBajaCerrado();
        }

        $actualizado = $inscripcion->update(['estatus' => 'dada_de_baja']);

        Cache::forget("malla_carrera_{$alumno->carrera_id}");

        return $actualizado;
    }

    /**
     * Lista las materias disponibles para inscribir con datos enriquecidos.
     * Usa la misma lógica del MallaCurricularService para respetar estados.
     */
    public function obtenerMateriasDisponibles(Alumno $alumno): Collection
    {
        $malla = app(MallaCurricularService::class)->obtenerMallaDelAlumno($alumno);

        $disponibles = collect();
        foreach ($malla as $semestre => $materias) {
            foreach ($materias as $materia) {
                // Disponibles e incluye reprobadas (puede recursar)
                if (in_array($materia['estado'], ['disponible', 'reprobada'])) {
                    $materia['semestre'] = $semestre;
                    $disponibles->push($materia);
                }
            }
        }

        return $disponibles->values();
    }

    /**
     * Lista las materias inscritas del alumno en el periodo actual con datos.
     */
    public function obtenerMateriasInscritas(Alumno $alumno, ?Periodo $periodo = null): Collection
    {
        $periodo ??= $this->obtenerPeriodoActivo();

        return $alumno->inscripciones()
            ->where('periodo_id', $periodo->id)
            ->where('estatus', 'en_curso')
            ->with('materiaMalla:id,clave,nombre,creditos,total_horas,semestre')
            ->get()
            ->map(fn($insc) => [
                'id' => $insc->id,
                'materia' => [
                    'id' => $insc->materiaMalla->id,
                    'clave' => $insc->materiaMalla->clave,
                    'nombre' => $insc->materiaMalla->nombre,
                    'creditos' => $insc->materiaMalla->creditos,
                    'total_horas' => $insc->materiaMalla->total_horas,
                    'semestre' => $insc->materiaMalla->semestre,
                ],
                'profesor' => $insc->profesor,
                'horario' => $insc->horario,
                'aula' => $insc->aula,
                'grupo' => $insc->grupo,
                'puede_darse_baja' => $periodo->estaAbiertoParaBaja(),
            ]);
    }

    /**
     * Valida un carrito antes de confirmar. Retorna array de errores por materia.
     * No lanza excepciones — útil para mostrar advertencias en tiempo real.
     */
    public function validarCarrito(Alumno $alumno, array $materiasIds): array
    {
        $errores = [];

        try {
            $this->validarAlumno($alumno);
            $periodo = $this->obtenerPeriodoActivo();
            $this->validarPeriodoAbierto($periodo);
        } catch (InscripcionException $e) {
            return ['general' => $e->getMessage()];
        }

        if (empty($materiasIds)) {
            return [];
        }

        $materias = MateriaMalla::whereIn('id', $materiasIds)->get();

        $inscripcionesPrevias = $alumno->inscripciones()
            ->select('materia_malla_id', 'estatus', 'periodo_id')
            ->get();

        $idsAprobadas = $inscripcionesPrevias->where('estatus', 'aprobada')
            ->pluck('materia_malla_id')->flip()->toArray();

        $idsActivas = $inscripcionesPrevias
            ->where('periodo_id', $periodo->id)
            ->whereIn('estatus', ['en_curso', 'aprobada'])
            ->pluck('materia_malla_id')->flip()->toArray();

        foreach ($materias as $materia) {
            try {
                $this->validarMateriaParaInscribir($materia, $idsAprobadas, $idsActivas);
            } catch (InscripcionException $e) {
                $errores[$materia->id] = $e->getMessage();
            }
        }

        return $errores;
    }

    // ─── Helpers privados ──────────────────────────────────────────

    private function validarMateriaParaInscribir(
        MateriaMalla $materia,
        array $idsAprobadas,
        array $idsActivas
    ): void {
        if (!$materia->activa) {
            throw InscripcionException::materiaInactiva($materia->nombre);
        }

        if (isset($idsAprobadas[$materia->id])) {
            throw InscripcionException::materiaYaAprobada($materia->nombre);
        }

        if (isset($idsActivas[$materia->id])) {
            throw InscripcionException::yaInscrita($materia->nombre);
        }

        // Prerrequisitos
        $prerreqs = $materia->prerrequisitos()->get(['id', 'clave', 'nombre']);
        $faltantes = [];

        foreach ($prerreqs as $prereq) {
            if (!isset($idsAprobadas[$prereq->id])) {
                $faltantes[] = "{$prereq->clave} ({$prereq->nombre})";
            }
        }

        if (!empty($faltantes)) {
            throw InscripcionException::prerrequisitosNoCumplidos(
                $materia->nombre,
                $faltantes
            );
        }
    }

    private function validarAlumno(Alumno $alumno): void
    {
        if ($alumno->estatus !== 'activo') {
            throw InscripcionException::alumnoInactivo();
        }
    }

    private function validarPeriodoAbierto(Periodo $periodo): void
    {
        if (!$periodo->estaAbiertoParaInscripcion()) {
            throw InscripcionException::periodoNoAbierto();
        }
    }

    private function obtenerPeriodoActivo(): Periodo
    {
        $periodo = Periodo::where('es_actual', true)->first();

        if (!$periodo) {
            throw InscripcionException::sinPeriodoActivo();
        }

        return $periodo;
    }
}