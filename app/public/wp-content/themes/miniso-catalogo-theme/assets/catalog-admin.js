(function($){
 'use strict';
 $(document).on('click','.mct-select-cover',function(e){
   e.preventDefault();
   var wrap=$(this).closest('td, .form-field');
   var frame=wp.media({title:'Imagen de portada MINISO',button:{text:'Usar imagen'},multiple:false,library:{type:'image'}});
   frame.on('select',function(){
     var obj=frame.state().get('selection').first().toJSON();
     wrap.find('input[name="mct_catalog_cover"]').val(obj.id);
     var url=(obj.sizes&&obj.sizes.thumbnail?obj.sizes.thumbnail.url:obj.url);
     wrap.find('.mct-cover-preview').empty().append($('<img>',{src:url,alt:'Portada seleccionada',width:120}));
   });
   frame.open();
 });
 $(document).on('click','.mct-remove-cover',function(e){
   e.preventDefault();var wrap=$(this).closest('td, .form-field');
   wrap.find('input[name="mct_catalog_cover"]').val('0');
   wrap.find('.mct-cover-preview').empty();
 });
})(jQuery);
