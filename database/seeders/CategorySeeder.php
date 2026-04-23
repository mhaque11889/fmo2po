<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Carpentry',
            'Mason',
            'Plumbing',
            'Housekeeping',
            'Horticulture',
            'Electrical',
            'AC/HVAC',
            'Air Quality',
            'Civil',
            'Housing Off Campus',
            'Housing On Campus',
        ];

        foreach ($categories as $index => $name) {
            Category::firstOrCreate(
                ['name' => $name],
                ['sort_order' => $index, 'is_active' => true]
            );
        }
    }
}
