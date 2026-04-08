<div class="card">
    <h2>Queue — Redis Worker</h2>

    <form action="{{ route('queue.dispatch') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn">Dispatch Test Job</button>
    </form>

    <div class="mt-4">
        <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.75rem;">Recent Job Results</h3>
        @if(count($queueResults) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_reverse(array_slice($queueResults, -5)) as $result)
                        <tr>
                            <td>{{ $result['message'] }}</td>
                            <td class="text-muted text-sm mono">{{ $result['timestamp'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted text-sm">No jobs processed yet. Dispatch a test job above.</p>
        @endif
    </div>
</div>
