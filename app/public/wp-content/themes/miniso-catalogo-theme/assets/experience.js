/* FUNCIÓN · El carrusel continúa tras un clic o un gesto; solo Pausar lo detiene de forma persistente. */
(() => {
  'use strict';
  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  document.querySelectorAll('[data-carousel]').forEach(root => {
    const track = root.querySelector('[data-carousel-track]');
    const slides = [...root.querySelectorAll('[data-carousel-slide]')];
    const dots = [...root.querySelectorAll('[data-carousel-dot]')];
    const pause = root.querySelector('[data-carousel-pause]');
    const status = root.querySelector('[data-carousel-status]');
    if (!track || slides.length < 2) return;
    let index = 0, timer, settle, resize;
    let userPaused = root.dataset.autoplay !== 'true';
    let hoverPaused = false, keyboardFocus = false, dragging = false;
    const interval = Math.max(4000, Number(root.dataset.interval) || 7000);
    function sync() {
      dots.forEach((dot, i) => i === index ? dot.setAttribute('aria-current', 'true') : dot.removeAttribute('aria-current'));
      slides.forEach((slide, i) => { slide.inert = i !== index; });
      if (pause) {
        pause.disabled = reduced.matches;
        pause.setAttribute('aria-pressed', String(userPaused || reduced.matches));
        pause.setAttribute('aria-label', reduced.matches ? 'Movimiento reducido activado en tu dispositivo' : userPaused ? 'Activar cambio automático' : 'Pausar cambio automático');
      }
    }
    function schedule() {
      clearTimeout(timer);
      if (!userPaused && !hoverPaused && !keyboardFocus && !dragging && !document.hidden && !reduced.matches) timer = setTimeout(() => go(index + 1, false), interval);
    }
    function go(next, manual = true) {
      index = (next + slides.length) % slides.length;
      sync();
      track.scrollTo({left: index * track.clientWidth, behavior: reduced.matches ? 'instant' : 'smooth'});
      if (manual && status) status.textContent = `Banner ${index + 1} de ${slides.length}`;
      schedule();
    }
    dots.forEach((dot, i) => dot.addEventListener('click', () => go(i)));
    root.querySelector('[data-carousel-prev]')?.addEventListener('click', () => go(index - 1));
    root.querySelector('[data-carousel-next]')?.addEventListener('click', () => go(index + 1));
    pause?.addEventListener('click', () => { userPaused = !userPaused; sync(); schedule(); });
    root.addEventListener('pointerenter', () => { if (root.dataset.hoverPause === 'true') { hoverPaused = true; schedule(); } });
    root.addEventListener('pointerleave', () => { hoverPaused = false; schedule(); });
    root.addEventListener('pointerdown', () => { keyboardFocus = false; schedule(); }, {passive: true});
    root.addEventListener('keydown', e => {
      if (e.key === 'Tab') { keyboardFocus = true; schedule(); }
    });
    root.addEventListener('focusin', e => { keyboardFocus = e.target !== pause && e.target.matches(':focus-visible'); schedule(); });
    root.addEventListener('focusout', e => { if (!root.contains(e.relatedTarget)) { keyboardFocus = false; schedule(); } });
    track.addEventListener('pointerdown', () => { dragging = true; schedule(); }, {passive: true});
    const release = () => { if (dragging) { dragging = false; schedule(); } };
    window.addEventListener('pointerup', release, {passive: true});
    window.addEventListener('pointercancel', release, {passive: true});
    track.addEventListener('keydown', e => {
      if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') { e.preventDefault(); go(index + (e.key === 'ArrowRight' ? 1 : -1)); }
    });
    track.addEventListener('scroll', () => {
      clearTimeout(settle);
      settle = setTimeout(() => {
        index = Math.max(0, Math.min(slides.length - 1, Math.round(track.scrollLeft / track.clientWidth)));
        sync(); schedule();
      }, 150);
    }, {passive: true});
    document.addEventListener('visibilitychange', schedule);
    reduced.addEventListener('change', () => { sync(); schedule(); });
    window.addEventListener('resize', () => {
      clearTimeout(resize);
      resize = setTimeout(() => { track.scrollTo({left: index * track.clientWidth, behavior: 'instant'}); }, 120);
    });
    sync(); schedule();
  });

  /* FUNCIÓN · Filtros locales rápidos. El formulario y los enlaces también funcionan sin JavaScript. */
  document.querySelectorAll('[data-store-locator]').forEach(root => {
    const form = root.querySelector('[data-store-form]');
    const cards = [...root.querySelectorAll('[data-store-id]')];
    const count = root.querySelector('[data-store-count]');
    const empty = root.querySelector('[data-store-empty]');
    const frame = root.querySelector('[data-store-map]');
    const placeholder = root.querySelector('[data-map-empty]');
    const panel = root.querySelector('.locator-map');
    let selected = cards.find(card => card.classList.contains('is-selected'));
    let debounce;
    const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
    function select(card, focus = false) {
      selected = card;
      cards.forEach(item => {
        const active = item === card;
        item.classList.toggle('is-selected', active);
        const link = item.querySelector('[data-store-select]');
        if (link) active ? link.setAttribute('aria-current', 'true') : link.removeAttribute('aria-current');
      });
      if (!frame) return;
      const src = card?.dataset.map || '';
      frame.hidden = !src; placeholder.hidden = Boolean(src);
      if (src && frame.getAttribute('src') !== src) frame.src = src;
      else if (!src) frame.removeAttribute('src');
      frame.title = `Mapa del centro comercial: ${card?.dataset.title || 'Guatemala'}`;
      root.querySelector('[data-map-title]').textContent = card?.dataset.title || 'Elige tu sucursal';
      root.querySelector('[data-map-address]').textContent = card?.dataset.address || '';
      const link = root.querySelector('[data-map-directions]');
      link.hidden = !card?.dataset.directions;
      if (card?.dataset.directions) link.href = card.dataset.directions;
      else link.removeAttribute('href');
      if (focus) {
        panel.focus({preventScroll: true});
        if (matchMedia('(max-width: 860px)').matches) panel.scrollIntoView({block: 'start', behavior: reduced.matches ? 'instant' : 'smooth'});
      }
    }
    function filter() {
      const query = normalize(form.elements.tienda.value);
      const region = form.elements.region.value;
      const department = form.elements.departamento.value;
      const visible = cards.filter(card => {
        const match = normalize(card.dataset.search).includes(query) && (!region || region === card.dataset.region) && (!department || department === card.dataset.department);
        card.hidden = !match;
        return match;
      });
      count.textContent = `${visible.length} ${visible.length === 1 ? 'sucursal' : 'sucursales'}`;
      empty.hidden = visible.length > 0;
      if (!visible.includes(selected)) select(visible[0] || null);
    }
    form.addEventListener('submit', e => { e.preventDefault(); clearTimeout(debounce); filter(); });
    form.elements.tienda.addEventListener('input', () => { clearTimeout(debounce); debounce = setTimeout(filter, 160); });
    form.elements.region.addEventListener('change', filter);
    form.elements.departamento.addEventListener('change', filter);
    root.querySelector('[data-store-clear]').addEventListener('click', e => {
      e.preventDefault(); form.elements.tienda.value = ''; form.elements.region.value = ''; form.elements.departamento.value = ''; filter(); form.elements.tienda.focus();
    });
    cards.forEach(card => card.querySelector('[data-store-select]')?.addEventListener('click', e => { e.preventDefault(); select(card, true); }));
    filter();
  });
})();
