<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Periodo;
use Carbon\Carbon;

class PeriodoSeeder extends Seeder {
    public function run(): void {
        $year = now()->year;
        $semestre = now()->month <= 6 ? 1 : 2;

        Periodo::create([
            'clave' => "$year-$semestre",
            'nombre' => $semestre === 1 ? "Primavera $year" : "Otoño $year",
            'fecha_inicio' => $semestre === 1
                ? Carbon::create($year, 1, 15)
                : Carbon::create($year, 8, 1),
            'fecha_fin' => $semestre === 1
                ? Carbon::create($year, 6, 30)
                : Carbon::create($year, 12, 15),
            'fecha_limite_inscripcion' => $semestre === 1
                ? Carbon::create($year, 1, 30)
                : Carbon::create($year, 8, 15),
            'fecha_limite_baja' => $semestre === 1
                ? Carbon::create($year, 4, 30)
                : Carbon::create($year, 10, 30),
            'es_actual' => true,
        ]);

        $this->command->info("✓ Periodo actual $year-$semestre creado");
    }
}