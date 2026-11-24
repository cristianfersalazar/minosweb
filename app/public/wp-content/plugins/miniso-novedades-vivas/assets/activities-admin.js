(function($){
  $(function(){
    var $list=$('.mnv-activity-list');
    var template=$('#mnv-activity-template').html();
    if(!$list.length || !template) return;
    var index=1000;
    $('.mnv-activity-add').on('click', function(){
      index++;
      $list.append(template.replaceAll('__INDEX__', String(index)));
    });
    $list.on('click', '.mnv-activity-remove', function(){ $(this).closest('.mnv-activity-row').remove(); });
    $list.on('click', '.mnv-activity-image-remove', function(){
      var $row=$(this).closest('.mnv-activity-row');
      $row.find('.mnv-activity-image-id').val('');
      $row.find('.mnv-activity-preview').empty();
    });
    $list.on('click','.mnv-activity-image', function(){
      if(!window.wp || !wp.media) return;
      var $row=$(this).closest('.mnv-activity-row');
      var frame=wp.media({title:'Seleccionar imagen',button:{text:'Usar imagen'},multiple:false,library:{type:'image'}});
      frame.on('select', function(){
        var attachment=frame.state().get('selection').first().toJSON();
        $row.find('.mnv-activity-image-id').val(attachment.id);
        var url=(attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url);
        $row.find('.mnv-activity-preview').empty().append($('<img>',{src:url,alt:'',css:{maxWidth:'100px',height:'auto'}}));
      });
      frame.open();
    });
  });
})(jQuery);
