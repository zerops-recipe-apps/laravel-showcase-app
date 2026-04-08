<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Meilisearch\Client as MeiliClient;

class DashboardController extends Controller
{
    public function index(): View
    {
        $services = $this->checkServices();

        $articles = Article::latest()->limit(15)->get();

        $cacheDemo = null;
        $demoValue = Cache::get('demo_cache_key');
        if ($demoValue !== null) {
            $cacheDemo = [
                'value' => $demoValue,
                'set_at' => Cache::get('demo_cache_key_set_at'),
            ];
        }

        $files = [];
        try {
            $disk = Storage::disk('s3');
            foreach ($disk->files('/') as $file) {
                $files[] = [
                    'name' => $file,
                    'size' => $disk->size($file),
                    'last_modified' => $disk->lastModified($file),
                ];
            }
        } catch (\Throwable) {
            // S3 unavailable
        }

        $queueResults = Cache::get('queue_results', []);

        return view('dashboard.index', compact('services', 'articles', 'cacheDemo', 'files', 'queueResults'));
    }

    public function health(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 503);
        }
    }

    public function status(): JsonResponse
    {
        $checks = [];

        $start = microtime(true);
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'connected',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            $checks['database'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        $start = microtime(true);
        try {
            Cache::store('redis')->put('_health_check', true, 10);
            Cache::store('redis')->forget('_health_check');
            $checks['redis'] = [
                'status' => 'connected',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            $checks['redis'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        $start = microtime(true);
        try {
            Storage::disk('s3')->files('/');
            $checks['storage'] = [
                'status' => 'connected',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            $checks['storage'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        $start = microtime(true);
        try {
            $client = new MeiliClient(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
            $client->health();
            $checks['search'] = [
                'status' => 'connected',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            $checks['search'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        return response()->json([
            'status' => collect($checks)->every(fn ($c) => $c['status'] === 'connected') ? 'ok' : 'degraded',
            'services' => $checks,
        ]);
    }

    private function checkServices(): array
    {
        $services = [];

        try {
            DB::connection()->getPdo();
            $services['database'] = ['status' => 'connected', 'label' => 'PostgreSQL'];
        } catch (\Throwable) {
            $services['database'] = ['status' => 'disconnected', 'label' => 'PostgreSQL'];
        }

        try {
            Cache::store('redis')->put('_health', true, 10);
            Cache::store('redis')->forget('_health');
            $services['redis'] = ['status' => 'connected', 'label' => 'Valkey (Redis)'];
        } catch (\Throwable) {
            $services['redis'] = ['status' => 'disconnected', 'label' => 'Valkey (Redis)'];
        }

        try {
            Storage::disk('s3')->files('/');
            $services['storage'] = ['status' => 'connected', 'label' => 'Object Storage (S3)'];
        } catch (\Throwable) {
            $services['storage'] = ['status' => 'disconnected', 'label' => 'Object Storage (S3)'];
        }

        try {
            $client = new MeiliClient(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
            $client->health();
            $services['search'] = ['status' => 'connected', 'label' => 'Meilisearch'];
        } catch (\Throwable) {
            $services['search'] = ['status' => 'disconnected', 'label' => 'Meilisearch'];
        }

        return $services;
    }
}
