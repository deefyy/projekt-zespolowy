document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('submit', event => {
        const form = event.target;
        // znajdź pierwszy widoczny przycisk typu submit w tym formularzu
        const btn = form.querySelector('button[type="submit"]:not([data-skip-lock])');
        if (!btn) return;

        // unikaj podwójnej blokady
        if (btn.dataset.locked) return;
        btn.dataset.locked = 'yes';

        // spinner SVG – możesz podmienić na własny!
        const spinner = `
          <svg class="w-5 h-5 ml-2 animate-spin"
               xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"/>
          </svg>`;

        btn.disabled = true;
        btn.classList.add('opacity-60', 'cursor-not-allowed');
        btn.insertAdjacentHTML('beforeend', spinner);

        if (btn.dataset.loadingText) {
            btn.querySelector('span')?.replaceWith(btn.dataset.loadingText);
        }
    });
});
