<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        $this->call([
            CarreraSeeder::class,
            MateriaMallaSeeder::class,
            PrerrequisitoSeeder::class,
            PeriodoSeeder::class,
            UsuariosInicialesSeeder::class,
        ]);
    }
}