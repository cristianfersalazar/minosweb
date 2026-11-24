/* FUNCIÓN · Selector nativo de la biblioteca de medios de WordPress. */
jQuery(function ($) {
  $(document).on('click', '.miniso-select-image', function () {
    const field = $(this).closest('.miniso-image-field');
    const frame = wp.media({ title: 'Seleccionar imagen', button: { text: 'Usar esta imagen' }, library: { type: 'image' }, multiple: false });
    frame.on('select', function () {
      const file = frame.state().get('selection').first().toJSON();
      field.find('input').val(file.id);
      field.find('.miniso-image-preview').empty().append($('<img>', { src: file.sizes?.medium?.url || file.url, alt: file.alt || '' }).css({maxWidth:'100%',height:'auto'}));
    });
    frame.open();
  }).on('click', '.miniso-remove-image', function () {
    const field = $(this).closest('.miniso-image-field');
    field.find('input').val('0');
    field.find('.miniso-image-preview').empty();
  });
});
