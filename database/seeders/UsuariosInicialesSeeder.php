<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Alumno;
use App\Models\ReglaAlerta;
use Illuminate\Support\Facades\Hash;

class UsuariosInicialesSeeder extends Seeder {
    public function run(): void {
        // ═══ TUTOR ═══
        $tutorUser = User::firstOrCreate(
            ['email' => 'tutor@tutoria.edu'],
            [
                'name'     => 'Tutor Principal',
                'password' => Hash::make('190038'),
                'rol'      => 'tutor',
                'activo'   => true,
            ]
        );

        $tutor = Tutor::firstOrCreate(
            ['usuario_id' => $tutorUser->id],
            [
                'numero_empleado' => '190038',
                'departamento'    => 'Facultad de Ciencias de la Información',
                'cubiculo'        => null,
                'grado_academico' => null,
            ]
        );

        // Crear reglas de alerta por defecto para el tutor
        $reglasDefault = [
            [
                'clave_regla' => 'calificacion_minima_materia',
                'descripcion' => 'Alerta cuando calificación en cualquier materia sea menor a',
                'umbral' => 70.00,
                'prioridad_alerta' => 'critica',
            ],
            [
                'clave_regla' => 'promedio_semestral_minimo',
                'descripcion' => 'Alerta cuando promedio semestral sea menor a',
                'umbral' => 80.00,
                'prioridad_alerta' => 'media',
            ],
            [
                'clave_regla' => 'caida_calificacion_puntos',
                'descripcion' => 'Alerta cuando caída entre parciales sea mayor a',
                'umbral' => 10.00,
                'prioridad_alerta' => 'media',
            ],
        ];

        foreach ($reglasDefault as $regla) {
            ReglaAlerta::firstOrCreate(
                ['tutor_id' => $tutor->id, 'clave_regla' => $regla['clave_regla']],
                array_merge(['activa' => true], $regla)
            );
        }

        // ═══ ALUMNO ═══
        $alumnoUser = User::firstOrCreate(
            ['email' => 'alumno@tutoria.edu'],
            [
                'name'     => 'Alumno Principal',
                'password' => Hash::make('190039'),
                'rol'      => 'alumno',
                'activo'   => true,
            ]
        );

        Alumno::firstOrCreate(
            ['matricula' => '190039'],
            [
                'usuario_id'      => $alumnoUser->id,
                'carrera_id'      => 1,
                'semestre_actual' => 1,
                'fecha_ingreso'   => now(),
                'tutor_id'        => $tutor->id,
                'estatus'         => 'activo',
            ]
        );

        $this->command->info('✓ Usuario tutor creado (matrícula/empleado: 190038)');
        $this->command->info('✓ Usuario alumno creado (matrícula: 190039)');
        $this->command->info('');
        $this->command->info('─────────────────────────────────────────');
        $this->command->info('ACCESOS:');
        $this->command->info('  Tutor:  tutor@tutoria.edu / 190038');
        $this->command->info('  Alumno: alumno@tutoria.edu / 190039');
        $this->command->info('─────────────────────────────────────────');
    }
}