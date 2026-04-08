<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} — Showcase Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-bg: #f8f9fa;
            --color-surface: #ffffff;
            --color-border: #e2e8f0;
            --color-text: #1a202c;
            --color-muted: #718096;
            --color-accent: #3b82f6;
            --color-accent-hover: #2563eb;
            --color-success: #10b981;
            --color-error: #ef4444;
            --color-warning: #f59e0b;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--color-bg);
            color: var(--color-text);
            line-height: 1.6;
            padding: 2rem 1rem;
        }

        .container { max-width: 960px; margin: 0 auto; }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--color-muted);
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card h2 {
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--color-border);
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
        }

        .service-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            border-radius: 6px;
            background: var(--color-bg);
            font-size: 0.9rem;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-dot.connected { background: var(--color-success); }
        .status-dot.disconnected { background: var(--color-error); }

        .placeholder-section {
            color: var(--color-muted);
            font-style: italic;
            text-align: center;
            padding: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.15s;
            text-decoration: none;
            color: #fff;
            background: var(--color-accent);
        }

        .btn:hover { background: var(--color-accent-hover); }

        .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }

        .btn-danger { background: var(--color-error); }
        .btn-danger:hover { background: #dc2626; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th, td {
            text-align: left;
            padding: 0.6rem 0.75rem;
            border-bottom: 1px solid var(--color-border);
        }

        th {
            font-weight: 600;
            color: var(--color-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tr:last-child td { border-bottom: none; }

        input[type="text"],
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            background: var(--color-surface);
            color: var(--color-text);
            transition: border-color 0.15s;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--color-accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-group { margin-bottom: 0.75rem; }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--color-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .flash {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .flash-success { background: #d1fae5; color: #065f46; }
        .flash-error { background: #fee2e2; color: #991b1b; }

        .mono { font-family: 'SF Mono', Monaco, 'Cascadia Mono', monospace; font-size: 0.85rem; }

        .text-muted { color: var(--color-muted); }
        .text-sm { font-size: 0.85rem; }

        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 0.5rem; }
        .gap-4 { gap: 1rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-4 { margin-top: 1rem; }
        .mb-2 { margin-bottom: 0.5rem; }

        @media (max-width: 640px) {
            body { padding: 1rem 0.5rem; }
            .services-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ config('app.name') }}</h1>
        <p class="subtitle">Showcase dashboard — PostgreSQL, Valkey, Object Storage, Meilisearch, Queue Worker</p>

        @if(session('success'))
            <div class="flash flash-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error">{{ session('error') }}</div>
        @endif

        {{-- Service Connectivity Panel --}}
        <div class="card">
            <h2>Service Connectivity</h2>
            <div class="services-grid">
                @foreach($services as $key => $service)
                    <div class="service-item">
                        <span class="status-dot {{ $service['status'] }}"></span>
                        <span>{{ $service['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Feature sections — populated by deploy step sub-agent --}}
        @include('dashboard.partials.articles')
        @include('dashboard.partials.cache')
        @include('dashboard.partials.storage')
        @include('dashboard.partials.search')
        @include('dashboard.partials.queue')
    </div>
</body>
</html>
