// Aperçu au survol des liens d'encyclopédie.
export default function initWikilinkPopover() {
    // Wikilink popover
    document.addEventListener('DOMContentLoaded', () => {
        const popover = document.createElement('div');
        popover.className = 'wikilink-popover';
        document.body.appendChild(popover);

        let activeLink = null;

        const hidePopover = () => {
            popover.style.opacity = '0';
            popover.style.transform = 'translateY(6px)';
            activeLink = null;
        };

        const showPopover = (link) => {
            const term = link.dataset.wikilinkTerm || link.textContent.trim();
            const teaser = link.dataset.wikilinkTeaser || '';
        
            popover.innerHTML = `
                <div class="font-serif text-arq-forest dark:text-arq-mint text-base mb-1 font-semibold">${term}</div>
                <p class="text-sm leading-relaxed">${teaser || 'Parchemin consultable dans l\'encyclopédie.'}</p>
            `;

            const rect = link.getBoundingClientRect();
            const top = window.scrollY + rect.bottom + 10;
            let left = window.scrollX + rect.left;
        
            // Ensure popover width is calculated
            popover.style.opacity = '0';
            popover.style.display = 'block';
            const popoverWidth = popover.offsetWidth;
        
            const maxLeft = window.scrollX + window.innerWidth - popoverWidth - 20;
            if (left > maxLeft) left = maxLeft;
            if (left < window.scrollX + 20) left = window.scrollX + 20;

            popover.style.left = `${left}px`;
            popover.style.top = `${top}px`;
            popover.style.opacity = '1';
            popover.style.transform = 'translateY(0)';
            activeLink = link;
        };

        document.addEventListener('mouseover', (e) => {
            const link = e.target.closest('.wikilink-resolved');
            if (link) {
                showPopover(link);
            } else if (!popover.contains(e.target)) {
                hidePopover();
            }
        });

        document.addEventListener('mouseout', (e) => {
            if (e.target.closest('.wikilink-resolved') && !e.relatedTarget?.closest('.wikilink-popover')) {
                setTimeout(hidePopover, 100);
            }
        });
    });
}
