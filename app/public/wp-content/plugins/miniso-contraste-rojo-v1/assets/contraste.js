(function () {
  'use strict';
  var mode = window.minisoRedTextMode || 'white';
  if (mode === 'off') return;

  // Incluye Inicio clásico, Inicio modular, bloques globales y el índice editorial de Novedades Vivas.
  var targets = [
    '.miniso-module-section', '.miniso-global-section', '.licenses-section',
    '.news-section', '.lover-section', '.stores-section', '.social-section',
    '.video-section', '.mct-news-index-hero', '.mct-news-story-head'
  ].join(', ');

  function rgbFromColor(value) {
    var found = String(value || '').match(/rgba?\(\s*(\d+(?:\.\d+)?)\s*[, ]+\s*(\d+(?:\.\d+)?)\s*[, ]+\s*(\d+(?:\.\d+)?)/i);
    return found ? [Number(found[1]), Number(found[2]), Number(found[3])] : null;
  }

  function isRedBackground(element) {
    var css = window.getComputedStyle(element);
    var rgb = rgbFromColor(css.backgroundColor);
    // El encabezado de Novedades Vivas usa --mnv-hero-bg y puede tener !important.
    if (!rgb && element.classList.contains('mnv-news-home-hero')) {
      var color = css.getPropertyValue('--mnv-hero-bg').trim();
      var probe = document.createElement('span');
      probe.style.color = color;
      if (probe.style.color) {
        // El color computado del elemento es preferible; usar variables únicamente como respaldo.
        var hex = color.match(/^#([a-f\d]{6})$/i);
        if (hex) rgb = [parseInt(hex[1].slice(0,2),16),parseInt(hex[1].slice(2,4),16),parseInt(hex[1].slice(4,6),16)];
      }
    }
    if (!rgb) return false;
    var r=rgb[0],g=rgb[1],b=rgb[2];
    return r >= 125 && r > g * 1.42 && r > b * 1.42 && g < 165 && b < 165;
  }

  function apply() {
    document.querySelectorAll(targets).forEach(function (element) {
      var red = isRedBackground(element);
      element.classList.toggle('miniso-contraste-rojo', red);
      if (red) element.style.setProperty('--miniso-red-foreground', mode === 'black' ? '#171717' : '#ffffff');
      else element.style.removeProperty('--miniso-red-foreground');
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', apply);
  else apply();
  window.addEventListener('load', apply, {once:true});
})();
