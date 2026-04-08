<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required|string|max:1000',
            'ttl' => 'required|integer|min:1|max:86400',
        ]);

        Cache::put($validated['key'], $validated['value'], (int) $validated['ttl']);

        // Also store metadata for the demo key
        if ($validated['key'] === 'demo_cache_key') {
            Cache::put('demo_cache_key_set_at', now()->toIso8601String(), (int) $validated['ttl']);
        }

        return redirect()->back()->with('success', "Cache key '{$validated['key']}' set for {$validated['ttl']} seconds.");
    }

    public function flush(Request $request)
    {
        Cache::flush();

        return redirect()->back()->with('success', 'Cache flushed successfully.');
    }
}
