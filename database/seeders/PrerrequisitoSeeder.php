<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MateriaMalla;
use Illuminate\Support\Facades\DB;

class PrerrequisitoSeeder extends Seeder {
    public function run(): void {
        $prerrequisitos = [
            // Inglés
            'ING401' => ['ING301'],
            'ING501' => ['ING401'],
            'ING601' => ['ING501'],

            // Matemáticas
            'CI401' => ['CD301'],
            'PE401' => ['MD301'],
            'IO601' => ['PE401'],
            'PMN701' => ['CI401'],

            // Programación
            'PROG201' => ['PROG101'],
            'ED301' => ['PROG201'],
            'POO401' => ['ED301'],
            'PV501' => ['POO401'],
            'IS601' => ['POO401'],
            'PA901' => ['PV501', 'IS601'],

            // Tratamiento de Información
            'BD301' => ['BD201'],
            'ADS401' => ['BD301'],
            'ADS501' => ['ADS401'],
            'DI701' => ['ADS501'],
            'ABD701' => ['BD301', 'PV501'],
            'LIU801' => ['DI701'],

            // Arquitectura
            'SEE501' => ['TS501'],
            'SD601' => ['SEE501'],
            'AC601' => ['SD601'],

            // Software de Base
            'LE601' => ['SO401', 'AC601'],
            'CE701' => ['LE601'],

            // Redes
            'SCS701' => ['RC601'],
            'SCS801' => ['SCS701'],

            // Entorno Social
            'CP401' => ['CF301'],
            'IMPL901' => ['ABD701', 'LIU801'],

            // Optativas
            'OPT701' => ['OPT601'],
            'OPT801' => ['OPT701'],
            'OPT901' => ['OPT801'],

            // Prácticas y Servicio Social
            'PP701' => ['ADS501', 'IS601'],
            'SS901' => ['PP701'],
        ];

        $creados = 0;

        foreach ($prerrequisitos as $materiaClave => $clavesPrerrequisitos) {
            $materia = MateriaMalla::where('clave', $materiaClave)->first();
            if (!$materia) continue;

            foreach ($clavesPrerrequisitos as $prerreqClave) {
                $prerreq = MateriaMalla::where('clave', $prerreqClave)->first();
                if (!$prerreq) continue;

                DB::table('prerrequisitos')->insert([
                    'materia_id' => $materia->id,
                    'prerrequisito_id' => $prerreq->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $creados++;
            }
        }

        $this->command->info("✓ $creados prerrequisitos creados");
    }
}