/* FUNCIÓN · Galería y favoritos. WordPress entrega las páginas y productos sin depender de JavaScript. */
(() => {
  'use strict';
  document.documentElement.classList.add('js');
  const key = window.minisoCatalog?.storageKey || 'miniso-colecciones';
  let favorites = new Set();
  try {
    const saved = JSON.parse(localStorage.getItem(key) || '[]');
    if (Array.isArray(saved)) favorites = new Set(saved.filter(id => typeof id === 'string'));
  } catch { /* La navegación funciona aunque se bloquee el almacenamiento local. */ }
  function syncFavorites() {
    document.querySelectorAll('[data-favorite]').forEach(button => {
      const selected = favorites.has(button.dataset.favorite);
      button.setAttribute('aria-pressed', String(selected));
      button.setAttribute('aria-label', `${selected ? 'Quitar de favoritos' : 'Guardar'} ${button.dataset.name}`);
    });
    document.querySelectorAll('[data-favorite-count]').forEach(el => { el.textContent = favorites.size; });
    const list = document.querySelector('[data-favorites-list]');
    if (list) {
      let count = 0;
      list.querySelectorAll('[data-collection]').forEach(card => {
        card.hidden = !favorites.has(card.dataset.collection);
        if (!card.hidden) count++;
      });
      list.hidden = false;
      document.querySelector('[data-favorites-empty]').hidden = count > 0;
    }
  }
  document.addEventListener('click', event => {
    const button = event.target.closest('[data-favorite]');
    if (!button) return;
    const id = button.dataset.favorite;
    favorites.has(id) ? favorites.delete(id) : favorites.add(id);
    let saved = true;
    try { localStorage.setItem(key, JSON.stringify([...favorites])); } catch { saved = false; }
    syncFavorites();
    const message = document.getElementById('message');
    message.textContent = !saved ? 'El navegador no permite guardar favoritos de forma permanente.' : favorites.has(id) ? 'Colección guardada en favoritos.' : 'Colección eliminada de favoritos.';
    clearTimeout(message.hideTimer);
    message.hideTimer = setTimeout(() => { message.textContent = ''; }, 3000);
  });
  syncFavorites();
  document.querySelectorAll('[data-gallery]').forEach(gallery => {
    const slides = [...gallery.querySelectorAll('[data-gallery-slide]')];
    const thumbs = [...gallery.querySelectorAll('[data-gallery-thumb]')];
    if (!slides.length) return;
    let index = 0;
    function show(next) {
      index = (next + slides.length) % slides.length;
      slides.forEach((slide, i) => { slide.hidden = i !== index; });
      thumbs.forEach((thumb, i) => { i === index ? thumb.setAttribute('aria-current', 'true') : thumb.removeAttribute('aria-current'); });
      const count = gallery.querySelector('[data-gallery-count]');
      if (count) count.textContent = `${index + 1} / ${slides.length}`;
    }
    thumbs.forEach((thumb, i) => thumb.addEventListener('click', event => { event.preventDefault(); show(i); }));
    gallery.querySelector('[data-gallery-prev]')?.addEventListener('click', () => show(index - 1));
    gallery.querySelector('[data-gallery-next]')?.addEventListener('click', () => show(index + 1));
    gallery.addEventListener('keydown', event => {
      if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
      event.preventDefault(); show(index + (event.key === 'ArrowRight' ? 1 : -1));
    });
  });
  const category = document.getElementById('mc_categoria');
  category?.addEventListener('change', () => {
    const subcategory = document.getElementById('mc_subcategoria');
    if (subcategory) subcategory.value = '0';
    category.form.requestSubmit();
  });
})();

/* FUNCIÓN · Carrusel con barrido, teclado, pausa y desplazamiento táctil nativo. */
(() => {
  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  document.querySelectorAll('[data-rail]').forEach(rail=>{
    const section=rail.closest('section');
    const move=direction=>rail.scrollBy({left:rail.clientWidth*.85*direction,behavior:reduced.matches?'instant':'smooth'});
    section.querySelector('[data-rail-prev]')?.addEventListener('click',()=>move(-1));
    section.querySelector('[data-rail-next]')?.addEventListener('click',()=>move(1));
  });
  document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.mobile-more[open]').forEach(x=>{x.open=false;x.querySelector('summary').focus();});});
  document.addEventListener('click',e=>{document.querySelectorAll('.mobile-more[open]').forEach(x=>{if(!x.contains(e.target))x.open=false;});});
  if(!reduced.matches&&document.body.classList.contains('miniso-animate')&&'IntersectionObserver' in window){
    const io=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){entry.target.animate([{opacity:.65,transform:'translateY(12px)'},{opacity:1,transform:'translateY(0)'}],{duration:450,easing:'ease-out'});io.unobserve(entry.target);}}),{threshold:.12});
    document.querySelectorAll('.section-head,.loyalty>div:last-child,.stores-callout>div,.social-section h2').forEach(el=>io.observe(el));
  }
})();
