<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Software & Apps',
                'slug' => 'software-apps',
                'description' => 'Unfinished applications, tools, and digital products.',
            ],
            [
                'name' => 'Art & Design',
                'slug' => 'art-design',
                'description' => 'Abandoned visual art, design work, and creative experiments.',
            ],
            [
                'name' => 'Writing & Stories',
                'slug' => 'writing-stories',
                'description' => 'Unfinished books, scripts, stories, and other writing.',
            ],
            [
                'name' => 'Inventions',
                'slug' => 'inventions',
                'description' => 'Prototypes and inventions that never reached completion.',
            ],
            [
                'name' => 'Business Ideas',
                'slug' => 'business-ideas',
                'description' => 'Business concepts and ventures that were set aside.',
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
