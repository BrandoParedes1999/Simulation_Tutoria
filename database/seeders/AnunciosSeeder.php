<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Anuncio;
use Carbon\Carbon;

class AnunciosSeeder extends Seeder
{
    public function run(): void
    {
        Anuncio::truncate();
        $y = now()->year;

        $data = [
            [
                'titulo'    => 'Periodo de inscripciones ' . $y . '-2 abierto',
                'contenido' => 'El periodo de inscripción para el semestre ' . $y . '-2 estará abierto hasta el 15 de agosto. Ingresa al sistema y selecciona tus materias antes de que se cierren los cupos. Recuerda revisar tus prerrequisitos.',
                'categoria' => 'urgente',
                'imagen_url'=> 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&q=80',
                'destacado' => true,
                'orden'     => 1,
                'fecha_expiracion' => Carbon::create($y, 8, 15),
            ],
            [
                'titulo'    => 'Convocatoria Servicio Social ' . $y,
                'contenido' => 'Alumnos que hayan cubierto el 70% de créditos pueden iniciar trámites. Acude a la coordinación con tu historial académico impreso y formato de solicitud. Vigencia todo el año.',
                'categoria' => 'academico',
                'imagen_url'=> 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&q=80',
                'destacado' => true,
                'orden'     => 2,
                'fecha_expiracion' => Carbon::create($y, 12, 31),
            ],
            [
                'titulo'    => 'Beca PRONABES ' . $y,
                'contenido' => 'Convocatoria abierta para alumnos con promedio mínimo de 80 puntos. Entrega documentos en Servicios Estudiantiles, edificio A, planta baja. Fecha límite: 28 de agosto.',
                'categoria' => 'beca',
                'imagen_url'=> 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=800&q=80',
                'destacado' => false,
                'orden'     => 3,
                'fecha_expiracion' => Carbon::create($y, 8, 28),
            ],
            [
                'titulo'    => 'Feria de Empresas UNACAR ' . $y,
                'contenido' => 'Conecta con más de 35 empresas en búsqueda de practicantes y egresados de ISC. Registro en línea obligatorio. Miércoles 20 de septiembre, Explanada Central, 9:00–14:00 h.',
                'categoria' => 'evento',
                'imagen_url'=> 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&q=80',
                'destacado' => false,
                'orden'     => 4,
                'fecha_expiracion' => Carbon::create($y, 9, 20),
            ],
            [
                'titulo'    => '2do. Congreso de Innovación Tecnológica',
                'contenido' => 'La Facultad invita al 2do. Congreso de Innovación Tecnológica con ponencias, talleres y hackathon. 10 y 11 de octubre, Auditorio Central.',
                'categoria' => 'evento',
                'imagen_url'=> 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&q=80',
                'destacado' => false,
                'orden'     => 5,
                'fecha_expiracion' => Carbon::create($y, 10, 11),
            ],
            [
                'titulo'    => 'Titulación por promedio — nueva modalidad',
                'contenido' => 'Alumnos con promedio general ≥ 90 y cero materias reprobadas pueden titularse por promedio. Consulta requisitos en el Departamento Escolar.',
                'categoria' => 'academico',
                'imagen_url'=> 'https://images.unsplash.com/photo-1549057446-9f5c6ac91a04?w=800&q=80',
                'destacado' => false,
                'orden'     => 6,
                'fecha_expiracion' => null,
            ],
            [
                'titulo'    => 'Olimpiada del Conocimiento ISC',
                'contenido' => 'Convocatoria para representar a UNACAR en la Olimpiada Regional. Áreas: Programación, Bases de Datos y Redes. Registro ante tu tutor.',
                'categoria' => 'academico',
                'imagen_url'=> 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=800&q=80',
                'destacado' => false,
                'orden'     => 7,
                'fecha_expiracion' => Carbon::create($y, 11, 30),
            ],
        ];

        foreach ($data as $item) {
            Anuncio::create(array_merge($item, ['activo' => true]));
        }

        $this->command->info('✓ ' . count($data) . ' anuncios creados');
    }
}