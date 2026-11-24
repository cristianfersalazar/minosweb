(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {
    const config = window.MINISO_HELP_TOPICS;
    const article = document.querySelector('article.page-content');
    if (!article || !config || !config.questions) return;
    const details = Array.from(article.querySelectorAll('details')).filter(function (d) {
      return d.querySelector(':scope > summary');
    });
    if (!details.length) return;
    const clean = function (v) {
      return (v || '').replace(/\s+/g, ' ').trim().normalize('NFC').toLocaleLowerCase();
    };
    const lookup = new Map();
    config.questions.forEach(function (entry) {
      lookup.set(clean(entry.question), entry.topic || '');
    });
    const questions = details.map(function (detail, index) {
      const label = clean(detail.querySelector('summary').textContent);
      let topic = lookup.get(label);
      // Respaldo para summaries con iconos/texto decorativo añadidos por otro plugin.
      if (topic === undefined && details.length === config.questions.length) {
        topic = config.questions[index].topic || '';
      }
      return { el: detail, topic: topic || '' };
    });
    const activeTopics = Object.keys(config.categories).filter(function (id) {
      return questions.some(function (q) { return q.topic === id; });
    });
    if (!activeTopics.length) return; // No introduce filtros vacíos sin clasificación previa.
    const nav = document.createElement('nav');
    nav.className = 'mac-help-tabs';
    nav.setAttribute('aria-label', 'Filtrar preguntas de ayuda');
    const filterIds = [''].concat(activeTopics);
    const buttons = [];
    const setActive = function (id) {
      questions.forEach(function (q) {
        const show = (id === '' || q.topic === id);
        q.el.hidden = !show;
        if (!show) q.el.open = false;
      });
      buttons.forEach(function (button) {
        const active = button.dataset.topic === id;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
    };
    filterIds.forEach(function (id) {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'mac-help-tab';
      button.dataset.topic = id;
      button.textContent = id === '' ? 'Todas' : config.categories[id];
      button.addEventListener('click', function () { setActive(id); });
      nav.appendChild(button);
      buttons.push(button);
    });
    const first = details[0];
    first.parentNode.insertBefore(nav, first);
    setActive('');
  });
})();
