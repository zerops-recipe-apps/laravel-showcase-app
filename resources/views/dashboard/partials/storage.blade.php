<div class="card">
    <h2>File Storage — Object Storage (S3)</h2>

    <div class="mb-2">
        <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.75rem;">Upload File</h3>
        <form action="{{ route('storage.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="storage-file">File (max 5MB)</label>
                <input type="file" id="storage-file" name="file" required>
            </div>
            <button type="submit" class="btn">Upload</button>
        </form>
    </div>

    <div class="mt-4">
        @if(count($files) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Size</th>
                        <th>Last Modified</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($files as $file)
                        <tr>
                            <td class="mono">{{ $file['name'] }}</td>
                            <td class="text-sm">@php
                                $bytes = $file['size'];
                                if ($bytes >= 1048576) {
                                    echo number_format($bytes / 1048576, 1) . ' MB';
                                } elseif ($bytes >= 1024) {
                                    echo number_format($bytes / 1024, 1) . ' KB';
                                } else {
                                    echo $bytes . ' B';
                                }
                            @endphp</td>
                            <td class="text-muted text-sm">{{ \Carbon\Carbon::createFromTimestamp($file['last_modified'])->diffForHumans() }}</td>
                            <td>
                                <form action="{{ route('storage.destroy', $file['name']) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted text-sm">No files uploaded yet.</p>
        @endif
    </div>
</div>
