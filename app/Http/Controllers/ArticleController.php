<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'body' => 'required|string',
            'category' => 'required|string|in:Technology,Science,Design,Business,Culture',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(6);
        $validated['published_at'] = now();

        Article::create($validated);

        return redirect()->back()->with('success', 'Article created successfully.');
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return redirect()->back()->with('success', 'Article deleted successfully.');
    }
}
