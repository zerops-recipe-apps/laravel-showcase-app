<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $results = Cache::get('queue_results', []);

        $results[] = [
            'message' => 'Job processed successfully',
            'timestamp' => now()->toIso8601String(),
        ];

        // Keep only the last 10 results
        $results = array_slice($results, -10);

        Cache::put('queue_results', $results, 86400);
    }
}
