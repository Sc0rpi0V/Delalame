document.addEventListener('DOMContentLoaded', () => {
    const galleryItems = document.querySelectorAll('[data-lightbox]');
    if (!galleryItems.length) return;

    // Build lightbox DOM
    const overlay = document.createElement('div');
    overlay.id = 'lightbox';
    overlay.className = 'fixed inset-0 z-50 bg-charcoal/90 flex items-center justify-center p-4 hidden';
    overlay.innerHTML = `
        <button id="lightbox-close" aria-label="Fermer" class="absolute top-4 right-4 text-warm-white hover:text-amber-craft transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <button id="lightbox-prev" aria-label="Précédent" class="absolute left-4 text-warm-white hover:text-amber-craft transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <button id="lightbox-next" aria-label="Suivant" class="absolute right-4 text-warm-white hover:text-amber-craft transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
        <figure class="max-w-5xl w-full">
            <img id="lightbox-img" src="" alt="" class="w-full max-h-[80vh] object-contain rounded-craft"/>
            <figcaption id="lightbox-caption" class="text-center text-warm-white/80 mt-3 text-sm font-sans"></figcaption>
        </figure>
    `;
    document.body.appendChild(overlay);

    const img = document.getElementById('lightbox-img');
    const caption = document.getElementById('lightbox-caption');
    let current = 0;
    const items = Array.from(galleryItems);

    const open = (index) => {
        current = index;
        const item = items[current];
        img.src = item.dataset.lightbox;
        img.alt = item.dataset.caption || '';
        caption.textContent = item.dataset.caption || '';
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    };

    items.forEach((item, i) => item.addEventListener('click', () => open(i)));
    document.getElementById('lightbox-close').addEventListener('click', close);
    document.getElementById('lightbox-prev').addEventListener('click', () => open((current - 1 + items.length) % items.length));
    document.getElementById('lightbox-next').addEventListener('click', () => open((current + 1) % items.length));
    overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });
    document.addEventListener('keydown', (e) => {
        if (overlay.classList.contains('hidden')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowLeft') open((current - 1 + items.length) % items.length);
        if (e.key === 'ArrowRight') open((current + 1) % items.length);
    });
});
