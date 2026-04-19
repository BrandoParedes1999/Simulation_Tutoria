<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Carrera;

class CarreraSeeder extends Seeder {
    public function run(): void {
        Carrera::create([
            'nombre' => 'Ingeniería en Sistemas Computacionales',
            'clave' => 'ISC',
            'total_semestres' => 9,
            'total_creditos' => 320,
            'total_horas' => 4864,
            'horas_servicio_social' => 480,
            'horas_practicas_profesionales' => 320,
            'plan' => '2010',
            'descripcion' => 'Programa Educativo de Licenciatura en Ingeniería en Sistemas Computacionales 2010 - UNACAR',
            'activa' => true,
        ]);
    }
}