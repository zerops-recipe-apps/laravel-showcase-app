<div class="card">
    <h2>Cache — Valkey (Redis)</h2>

    @if($cacheDemo)
        <div style="background: var(--color-bg); padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem;">
            <p class="text-sm"><strong>demo_cache_key</strong></p>
            <p class="mono">{{ $cacheDemo['value'] }}</p>
            @if($cacheDemo['set_at'])
                <p class="text-muted text-sm mt-2">Set at: {{ $cacheDemo['set_at'] }}</p>
            @endif
        </div>
    @else
        <p class="text-muted text-sm mb-2">No value stored for <span class="mono">demo_cache_key</span>. Use the form below to set one.</p>
    @endif

    <div class="mt-4">
        <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.75rem;">Set Cache Key</h3>
        <form action="{{ route('cache.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="cache-key">Key</label>
                <input type="text" id="cache-key" name="key" value="demo_cache_key" required>
            </div>
            <div class="form-group">
                <label for="cache-value">Value</label>
                <input type="text" id="cache-value" name="value" required>
            </div>
            <div class="form-group">
                <label for="cache-ttl">TTL (seconds)</label>
                <input type="text" id="cache-ttl" name="ttl" value="300" required>
            </div>
            <button type="submit" class="btn">Set Cache Key</button>
        </form>
    </div>

    <div class="mt-4">
        <form action="{{ route('cache.flush') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-danger">Flush Cache</button>
        </form>
    </div>
</div>
