/* MINISO Novedades: aparición progresiva con accesibilidad y respaldo sin JS. */
(function () {
  'use strict';
  function ready() {
    if (!('IntersectionObserver' in window) ||
        (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) return;
    var targets = document.querySelectorAll(
      '.mct-news-featured, .mct-news-tile, .mct-news-story-head .mct-news-story-wrap, ' +
      '.mct-news-story-cover, .mnv-news-prizes-head, .mnv-gift-card, .mnv-video-card, ' +
      '.mct-news-event-info, .mct-news-instagram, .mct-news-related, .mnv-universal-activities__intro, .mnv-universal-activities__card'
    );
    if (!targets.length) return;
    targets.forEach(function (element) { element.classList.add('mnv-reveal'); });
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, {threshold: 0.06, rootMargin: '0px 0px 60px 0px'});
    targets.forEach(function (el) { observer.observe(el); });
    document.documentElement.classList.add('mnv-can-animate');
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
  else ready();
}());
