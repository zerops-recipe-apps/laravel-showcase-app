<div class="card">
    <h2>Articles — Database (PostgreSQL)</h2>

    @if($articles->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Published</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($articles as $article)
                    <tr>
                        <td>{{ $article->title }}</td>
                        <td>{{ $article->category }}</td>
                        <td class="text-muted text-sm">{{ $article->published_at?->diffForHumans() ?? 'Draft' }}</td>
                        <td>
                            <form action="{{ route('articles.destroy', $article) }}" method="POST" style="display:inline;">
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
        <p class="text-muted text-sm">No articles yet.</p>
    @endif

    <div class="mt-4">
        <h3 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.75rem;">Create Article</h3>
        <form action="{{ route('articles.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="article-title">Title</label>
                <input type="text" id="article-title" name="title" required>
            </div>
            <div class="form-group">
                <label for="article-excerpt">Excerpt</label>
                <input type="text" id="article-excerpt" name="excerpt" required>
            </div>
            <div class="form-group">
                <label for="article-body">Body</label>
                <textarea id="article-body" name="body" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="article-category">Category</label>
                <select id="article-category" name="category" required>
                    <option value="">Select category...</option>
                    <option value="Technology">Technology</option>
                    <option value="Science">Science</option>
                    <option value="Design">Design</option>
                    <option value="Business">Business</option>
                    <option value="Culture">Culture</option>
                </select>
            </div>
            <button type="submit" class="btn">Create Article</button>
        </form>
    </div>
</div>
