<?php

namespace Database\Seeders;

use App\Models\FormationCategory;
use Illuminate\Database\Seeder;

class FormationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'TBT', 'code' => 'TBT'],
            ['name' => 'Mondater', 'code' => 'MONDATER'],
            ['name' => 'LFI', 'code' => 'LFI'],
            ['name' => 'Notes', 'code' => 'NOTES'],
            ['name' => 'Continue', 'code' => 'CONTINUE'],
        ];

        foreach ($categories as $category) {
            FormationCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }
    }
}

