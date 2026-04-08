<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Article::count() === 0) {
            Article::factory(20)->create();
        }
    }
}
