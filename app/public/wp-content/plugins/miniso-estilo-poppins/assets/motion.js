/* FUNCIÓN · Anima una sola vez los textos al entrar en pantalla. El contenido siempre existe y es visible. */
(() => {
  'use strict';
  const start = () => {
    if (!document.body.classList.contains('miniso-motion')) return;
    if (!('IntersectionObserver' in window) || !Element.prototype.animate) return;
    const preference = window.matchMedia('(prefers-reduced-motion: reduce)');
    const running = new Set();
    let observer;
    const stop = () => {
      observer?.disconnect();
      running.forEach(animation => animation.cancel());
      running.clear();
    };
    if (preference.matches) return;
    observer = new IntersectionObserver(entries => {
      for (const entry of entries) {
        if (!entry.isIntersecting) continue;
        observer.unobserve(entry.target);
        if (preference.matches) continue;
        // EDITAR · 550 milisegundos y 12 píxeles: entrada sutil sin mover la distribución.
        const animation = entry.target.animate([
          { opacity: 0.45, transform: 'translateY(12px)' },
          { opacity: 1, transform: 'translateY(0)' }
        ], { duration: 550, easing: 'cubic-bezier(.2,.65,.3,1)', fill: 'none' });
        running.add(animation);
        const cleanup = () => running.delete(animation);
        animation.addEventListener('finish', cleanup, { once: true });
        animation.addEventListener('cancel', cleanup, { once: true });
      }
    }, { threshold: 0.12, rootMargin: '0px 0px -18px 0px' });
    document.querySelectorAll('.section-head h1, .section-head h2, .page-content > h1, main > h1, .collection-intro h1, .product-copy > h1, .loyalty h2, .card-body, .store h3, .miniso-animar').forEach(element => observer.observe(element));
    preference.addEventListener('change', event => { if (event.matches) stop(); });
    // FUNCIÓN · El foco del teclado conserva el texto quieto mientras se utiliza un control.
    document.addEventListener('focusin', event => {
      running.forEach(animation => {
        if (animation.effect?.target?.contains(event.target)) animation.cancel();
      });
    });
    window.addEventListener('pagehide', stop, { once: true });
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
})();
