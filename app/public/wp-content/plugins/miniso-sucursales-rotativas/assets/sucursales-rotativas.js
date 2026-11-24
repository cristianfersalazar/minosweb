/* MINISO · Sucursales rotativas: sin dependencias ni cambios al diseño. */
(function () {
  'use strict';

  function shuffle(values) {
    var result = values.slice();
    for (var i = result.length - 1; i > 0; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      var temp = result[i];
      result[i] = result[j];
      result[j] = temp;
    }
    return result;
  }

  function readState(key) {
    try {
      var raw = localStorage.getItem(key);
      var data = raw ? JSON.parse(raw) : null;
      if (data && typeof data === 'object' && Array.isArray(data.last) && data.counts) return data;
    } catch (error) { /* Navegador sin almacenamiento: aleatorio igualmente. */ }
    return {last: [], counts: {}};
  }

  function writeState(key, data) {
    try { localStorage.setItem(key, JSON.stringify(data)); } catch (error) { /* Opcional. */ }
  }

  function choose(items, count, state) {
    var last = new Set(state.last);
    var counts = state.counts || {};
    // Aleatoriedad en empates, prioridad a sucursales menos vistas.
    var shuffled = shuffle(items);
    var sortByExposure = function (a, b) {
      return (Number(counts[a.key]) || 0) - (Number(counts[b.key]) || 0);
    };
    var unseenThisVisit = shuffled.filter(function (item) { return !last.has(item.key); }).sort(sortByExposure);
    var shownLastTime = shuffled.filter(function (item) { return last.has(item.key); }).sort(sortByExposure);
    var selected = unseenThisVisit.concat(shownLastTime).slice(0, count);
    return selected;
  }

  function rotateOne(container, number) {
    var cards = Array.prototype.slice.call(container.children).filter(function (el) {
      return el.classList.contains('miniso-module-store');
    });
    if (!cards.length) return;
    var count = Math.max(1, Math.min(cards.length, number || 3));
    var items = cards.map(function (card, index) {
      var title = card.querySelector('h3');
      return {element: card, key: (title ? title.textContent.trim() : '') + '#' + index};
    });
    var stateKey = 'miniso-sucursales-portada-v1:' + number;
    var state = readState(stateKey);
    var selected = choose(items, count, state);
    var selectedElements = new Set(selected.map(function (item) { return item.element; }));

    // Visibilidad y orden al azar de los elementos, sin modificar su contenido.
    selected.forEach(function (item) { container.appendChild(item.element); });
    cards.forEach(function (card) { card.hidden = !selectedElements.has(card); });
    state.last = selected.map(function (item) { return item.key; });
    selected.forEach(function (item) { state.counts[item.key] = (Number(state.counts[item.key]) || 0) + 1; });
    // Eliminar entradas obsoletas si se borraron sucursales del administrador.
    var current = new Set(items.map(function (item) { return item.key; }));
    Object.keys(state.counts).forEach(function (key) {
      if (!current.has(key)) delete state.counts[key];
    });
    writeState(stateKey, state);
    container.setAttribute('data-miniso-rotated', '1');
  }

  function init() {
    var containers = document.querySelectorAll('.miniso-module-stores[data-miniso-rotate="1"]');
    Array.prototype.forEach.call(containers, function (container) {
      var requested = parseInt(container.getAttribute('data-miniso-count') || '3', 10);
      rotateOne(container, requested);
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
