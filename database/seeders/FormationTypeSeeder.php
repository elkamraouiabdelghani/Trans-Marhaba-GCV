<?php

namespace Database\Seeders;

use App\Models\FormationType;
use Illuminate\Database\Seeder;

class FormationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Conduite défensive', 'code' => 'COND_DEF', 'description' => 'Techniques de conduite défensive', 'is_active' => true],
            ['name' => 'Sécurité routière', 'code' => 'SEC_ROUT', 'description' => 'Règles et bonnes pratiques de sécurité routière', 'is_active' => true],
            ['name' => 'ADR / Matières dangereuses', 'code' => 'ADR', 'description' => 'Transport de matières dangereuses', 'is_active' => true],
            ['name' => 'Habilitation citerne', 'code' => 'HAB_CIT', 'description' => 'Manipulation et sécurité des citernes', 'is_active' => true],
            ['name' => 'Premiers secours', 'code' => 'PSC1', 'description' => 'Gestes de premiers secours', 'is_active' => true],
            ['name' => 'Éco-conduite', 'code' => 'ECO_COND', 'description' => 'Réduction de la consommation et conduite éco-responsable', 'is_active' => true],
        ];

        FormationType::upsert($types, ['code'], ['name', 'description', 'is_active']);
    }
}


