<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Alumno;
use App\Models\Tutor;

/**
 * AlumnosPreRegistradosSeeder
 *
 * Crea alumnos que YA EXISTEN en Control Escolar pero aún no tienen cuenta
 * en el sistema. Sirven para demostrar el flujo de registro por matrícula.
 *
 * IMPORTANTE: el dominio debe ser @mail.unacar.mx para coincidir con la
 * constante DOMINIO del componente RegistroAlumno, que genera el correo
 * como: strtolower($matricula) . '@mail.unacar.mx'
 *
 * Para probar el registro:
 *   1. Ve a /registro/alumno
 *   2. Ingresa una de las matrículas de abajo
 *   3. El código llega a storage/logs/laravel.log (busca "CÓDIGO:")
 */
class AlumnosPreRegistradosSeeder extends Seeder
{
    public function run(): void
    {
        $tutor = Tutor::first();

        if (!$tutor) {
            $this->command->warn('No se encontró ningún tutor. Ejecuta primero UsuariosInicialesSeeder.');
            return;
        }

        $alumnos = [
            [
                'matricula'            => '220101',
                'correo_institucional' => '220101@mail.unacar.mx',   // Fix: era @unacar.edu.mx
                'semestre_actual'      => 2,
                'creditos_aprobados'   => 31,
                'promedio_general'     => 84.5,
            ],
            [
                'matricula'            => '210045',
                'correo_institucional' => '210045@mail.unacar.mx',   // Fix: era @unacar.edu.mx
                'semestre_actual'      => 5,
                'creditos_aprobados'   => 124,
                'promedio_general'     => 83.6,
            ],
            [
                'matricula'            => '200321',
                'correo_institucional' => '200321@mail.unacar.mx',   // Fix: era @unacar.edu.mx
                'semestre_actual'      => 7,
                'creditos_aprobados'   => 208,
                'promedio_general'     => 91.2,
            ],
            [
                'matricula'            => '230187',
                'correo_institucional' => '230187@mail.unacar.mx',   // Fix: era @unacar.edu.mx
                'semestre_actual'      => 1,
                'creditos_aprobados'   => 0,
                'promedio_general'     => null,
            ],
            [
                'matricula'            => '220555',
                'correo_institucional' => '220555@mail.unacar.mx',   // Fix: era @unacar.edu.mx
                'semestre_actual'      => 3,
                'creditos_aprobados'   => 59,
                'promedio_general'     => 77.8,
            ],
        ];

        $creados = 0;

        foreach ($alumnos as $datos) {
            if (Alumno::where('matricula', $datos['matricula'])->exists()) {
                continue;
            }

            Alumno::create([
                'usuario_id'           => null,
                'matricula'            => $datos['matricula'],
                'correo_institucional' => $datos['correo_institucional'],
                'carrera_id'           => 1,
                'semestre_actual'      => $datos['semestre_actual'],
                'fecha_ingreso'        => now()->subYears((int) (($datos['semestre_actual'] - 1) / 2)),
                'tutor_id'             => $tutor->id,
                'estatus'              => 'activo',
                'creditos_aprobados'   => $datos['creditos_aprobados'],
                'promedio_general'     => $datos['promedio_general'],
            ]);

            $creados++;
        }

        $this->command->info("✓ {$creados} alumnos pre-registrados creados (sin cuenta de usuario)");
        $this->command->line('');
        $this->command->line('  Matrículas disponibles para probar /registro/alumno:');
        foreach ($alumnos as $a) {
            $this->command->line("    {$a['matricula']}  →  {$a['correo_institucional']}");
        }
        $this->command->line('');
        $this->command->line('  💡 En desarrollo el código de verificación llega a:');
        $this->command->line('     storage/logs/laravel.log  (busca "CÓDIGO:")');
    }
}