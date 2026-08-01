// Recherche instantanée de l'en-tête.
export default function initRecherche() {
    const searchToggle = document.getElementById('search-toggle');
    const searchContainer = document.getElementById('search-container');
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    let searchTimeout;

    searchToggle?.addEventListener('click', (e) => {
        e.stopPropagation();
        searchContainer.classList.toggle('hidden');
        if (!searchContainer.classList.contains('hidden')) {
            searchInput.focus();
        }
    });

    searchInput?.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const q = e.target.value.trim();
    
        if (q.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }
    
        searchTimeout = setTimeout(async () => {
            const res = await fetch(`/api/recherche?q=${encodeURIComponent(q)}`);
            const data = await res.json();
        
            if (data.length === 0) {
                searchResults.innerHTML = '<div class="p-3 text-arq-bark/60 text-sm">Aucun savoir trouvé...</div>';
            } else {
                searchResults.innerHTML = data.map(r => `
                    <a href="${r.url}" class="flex items-center gap-3 px-3 py-2 hover:bg-arq-parchment-dark border-b border-arq-amber/20 last:border-0">
                        ${r.thumbnail 
                            ? `<div class="w-10 h-10 rounded-organic-sm flex-shrink-0 overflow-hidden bg-arq-parchment-dark"><img src="${r.thumbnail}" class="w-full h-full object-cover scale-150" alt=""></div>` 
                            : `<span class="w-10 h-10 flex items-center justify-center bg-arq-parchment-dark rounded-organic-sm flex-shrink-0 text-sm text-arq-bark">${r.type === 'book' ? '📚' : '📜'}</span>`}
                        <span class="text-sm text-arq-ink truncate">${r.title}</span>
                    </a>
                `).join('');
            }
            searchResults.classList.remove('hidden');
        }, 200);
    });

    document.addEventListener('click', (e) => {
        if (!searchContainer?.contains(e.target) && e.target !== searchToggle) {
            searchContainer?.classList.add('hidden');
            searchResults?.classList.add('hidden');
        }
    });
}
