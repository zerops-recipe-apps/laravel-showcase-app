<div class="card">
    <h2>Search — Meilisearch</h2>

    <div class="form-group">
        <label for="search-input">Search Articles</label>
        <input type="text" id="search-input" placeholder="Type to search articles...">
    </div>

    <div id="search-results" class="mt-2">
        <p class="text-muted text-sm">Type a query above to search articles.</p>
    </div>
</div>

<script>
(function() {
    let debounceTimer = null;
    const input = document.getElementById('search-input');
    const resultsContainer = document.getElementById('search-results');

    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            const query = input.value.trim();
            if (query.length === 0) {
                resultsContainer.innerHTML = '';
                const p = document.createElement('p');
                p.className = 'text-muted text-sm';
                p.textContent = 'Type a query above to search articles.';
                resultsContainer.appendChild(p);
                return;
            }

            fetch('/search?q=' + encodeURIComponent(query))
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    resultsContainer.innerHTML = '';

                    if (data.length === 0) {
                        const p = document.createElement('p');
                        p.className = 'text-muted text-sm';
                        p.textContent = 'No results found for "' + query + '".';
                        resultsContainer.appendChild(p);
                        return;
                    }

                    data.forEach(function(item) {
                        const div = document.createElement('div');
                        div.style.cssText = 'padding: 0.6rem 0; border-bottom: 1px solid var(--color-border);';

                        const titleRow = document.createElement('div');
                        titleRow.className = 'flex items-center gap-2';

                        const title = document.createElement('strong');
                        title.textContent = item.title;

                        const badge = document.createElement('span');
                        badge.style.cssText = 'font-size: 0.75rem; background: var(--color-bg); padding: 0.15rem 0.5rem; border-radius: 4px; color: var(--color-muted);';
                        badge.textContent = item.category;

                        titleRow.appendChild(title);
                        titleRow.appendChild(badge);

                        const excerpt = document.createElement('p');
                        excerpt.className = 'text-muted text-sm';
                        excerpt.style.marginTop = '0.25rem';
                        excerpt.textContent = item.excerpt;

                        div.appendChild(titleRow);
                        div.appendChild(excerpt);
                        resultsContainer.appendChild(div);
                    });
                })
                .catch(function() {
                    resultsContainer.innerHTML = '';
                    const p = document.createElement('p');
                    p.className = 'text-sm';
                    p.style.color = 'var(--color-error)';
                    p.textContent = 'Search request failed.';
                    resultsContainer.appendChild(p);
                });
        }, 300);
    });
})();
</script>
