<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(rand(4, 8));

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(2),
            'body' => implode("\n\n", fake()->paragraphs(rand(3, 6))),
            'category' => fake()->randomElement([
                'Technology', 'Science', 'Design', 'Business', 'Culture',
            ]),
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
