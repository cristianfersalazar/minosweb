(function($){'use strict';
 $(document).on('click','.mct-team-media-pick',function(e){
  e.preventDefault();
  const holder=$(this).closest('.mct-team-media');
  if(!window.wp||!wp.media){return;}
  const dialog=wp.media({title:'Seleccionar fotografía oficial MINISO',button:{text:'Usar fotografía'},multiple:false,library:{type:'image'}});
  dialog.on('select',function(){
    const chosen=dialog.state().get('selection').first(); if(!chosen)return;
    const data=chosen.toJSON(); holder.find('.mct-team-media-value').val(data.id||0);
    const thumb=(data.sizes&&data.sizes.thumbnail&&data.sizes.thumbnail.url)||data.url||'';
    holder.find('.mct-team-media-image').empty();
    if(thumb) $('<img>',{src:thumb,alt:'Vista previa de fotografía',class:'mct-team-media-preview'}).appendTo(holder.find('.mct-team-media-image'));
  }); dialog.open();
 });
 $(document).on('click','.mct-team-media-clear',function(e){e.preventDefault();const h=$(this).closest('.mct-team-media');h.find('.mct-team-media-value').val(0);h.find('.mct-team-media-image').empty();});
})(jQuery);
