<?php
/** Template Name: MINISO · Sucursales */
defined('ABSPATH') || exit;
get_header();
if (!mct_ready()) { mct_dependency_notice(); get_footer(); return; }
$base=get_permalink();
$stores=get_posts(array('post_type'=>'miniso_sucursal','post_status'=>'publish','has_password'=>false,'numberposts'=>-1,'orderby'=>array('menu_order'=>'ASC','title'=>'ASC')));
$search=strtolower(remove_accents(mct_get('tienda')));
$region=mct_get('region'); $department=mct_get('departamento');
$regions=array('capital'=>'Capital y alrededores','interior'=>'Interior del país');
$records=array(); $departments=array(); $visible=array();
foreach($stores as $store){
    $id=$store->ID; $r=array('post'=>$store);
    foreach(array('mall','address','hours','department','hours_basis','region','map') as $key) $r[$key]=(string)get_post_meta($id,'miniso_'.$key,true);
    if($r['department']) $departments[$r['department']]=$r['department'];
    $r['search']=strtolower(remove_accents($store->post_title.' '.$r['mall'].' '.$r['address'].' '.$r['department'].' '.wp_strip_all_tags($store->post_content)));
    $r['visible']=(''===$search||str_contains($r['search'],$search))&&(''===$region||$region===$r['region'])&&(''===$department||$department===$r['department']);
    $r['query']=$r['address']?trim($r['mall'].' '.$r['address'].' Guatemala'):'';
    $r['embed']=$r['query']?'https://www.google.com/maps?'.http_build_query(array('q'=>$r['query'],'output'=>'embed')):'';
    $r['directions']=$r['map']?:($r['query']?'https://www.google.com/maps/dir/?'.http_build_query(array('api'=>1,'destination'=>$r['query'])):'');
    $records[$id]=$r; if($r['visible'])$visible[]=$id;
}
natcasesort($departments);
$selected=absint(mct_get('tienda_id'));
if(!in_array($selected,$visible,true))$selected=$visible[0]??0;
$active=$records[$selected]??null;
?>
<div class="locator-heading"><div class="locator-mark"><?php echo mct_icon('sucursales'); ?></div><div><span class="eyebrow"><?php echo esc_html(mct_copy_text('locator_eyebrow')); ?></span><h1><?php echo esc_html(mct_design('locator_title')); ?></h1><p><?php echo esc_html(mct_design('locator_text')); ?></p></div></div>
<?php while(have_posts()){the_post(); if(trim(get_the_content()) && '<p>Encuentra tu sucursal y consulta directamente los horarios y la disponibilidad.</p>'!==trim(get_the_content())) the_content();} ?>
<section class="store-locator <?php echo mct_design('show_store_map')?'':'locator-no-map'; ?>" data-store-locator aria-label="Localizador de sucursales">
  <form class="locator-filters" method="get" action="<?php echo esc_url($base); ?>" data-store-form>
    <?php mct_form_hidden($base); ?>
    <label class="locator-search">Buscar una tienda<input type="search" name="tienda" value="<?php echo esc_attr(mct_get('tienda')); ?>" placeholder="Tienda, centro comercial o dirección" autocomplete="off"></label>
    <label>Región<select name="region"><option value="">Todas las regiones</option><?php foreach($regions as $value=>$label) echo '<option value="'.esc_attr($value).'" '.selected($region,$value,false).'>'.esc_html($label).'</option>'; ?></select></label>
    <label>Departamento<select name="departamento"><option value="">Todos los departamentos</option><?php foreach($departments as $value) echo '<option '.selected($department,$value,false).'>'.esc_html($value).'</option>'; ?></select></label>
    <button class="button" type="submit">Buscar</button><a class="text-link" data-store-clear href="<?php echo esc_url($base); ?>">Limpiar</a>
  </form>
  <div class="locator-results"><span data-store-count role="status"><?php echo count($visible).' '.(1===count($visible)?'sucursal':'sucursales'); ?></span><span><?php echo esc_html(mct_copy_text('locator_results_phrase')); ?></span></div>
  <div class="locator-body">
    <div class="locator-list" aria-label="Sucursales encontradas">
      <?php foreach($records as $id=>$r): $store=$r['post']; ?>
      <article class="locator-store <?php echo $id===$selected?'is-selected':''; ?>" data-store-id="<?php echo (int)$id; ?>" data-region="<?php echo esc_attr($r['region']); ?>" data-department="<?php echo esc_attr($r['department']); ?>" data-search="<?php echo esc_attr($r['search']); ?>" data-title="<?php echo esc_attr($store->post_title); ?>" data-address="<?php echo esc_attr($r['address']); ?>" data-map="<?php echo esc_url($r['embed']); ?>" data-directions="<?php echo esc_url($r['directions']); ?>" <?php echo $r['visible']?'':'hidden'; ?>>
        <span class="store-region"><?php echo esc_html($regions[$r['region']]??'Guatemala'); ?></span>
        <h2><?php echo esc_html($store->post_title); ?></h2>
        <?php if($r['mall'] && $r['mall']!==$store->post_title): ?><p class="store-mall"><?php echo esc_html($r['mall']); ?></p><?php endif; ?>
        <div class="store-detail"><?php echo mct_icon('sucursales'); ?><div><strong>Dirección</strong><p><?php echo $r['address']?nl2br(esc_html($r['address'])):'Dirección pendiente de confirmar.'; ?></p></div></div>
        <div class="store-detail"><?php echo mct_icon('clock'); ?><div><strong><?php echo 'store'===$r['hours_basis']?'Horario de MINISO':'Horario de referencia'; ?></strong><p><?php echo $r['hours']?nl2br(esc_html($r['hours'])):'Horario pendiente de confirmar.'; ?></p><?php if($r['hours'] && 'mall'===$r['hours_basis']): ?><span class="store-reference">Horario del centro comercial</span><?php endif; ?></div></div>
        <?php if(trim($store->post_content)): ?><div class="store-extra"><?php echo wp_kses_post(wpautop($store->post_content)); ?></div><?php endif; ?>
        <div class="store-actions">
          <?php if(mct_design('show_store_map') && $r['embed']): ?><a class="button light store-select" href="<?php echo esc_url(add_query_arg(array('tienda_id'=>$id,'tienda'=>mct_get('tienda'),'region'=>$region,'departamento'=>$department),$base).'#mapa-sucursal'); ?>" data-store-select <?php echo $id===$selected?'aria-current="true"':''; ?>><?php echo esc_html(mct_design('map_button')); ?></a><?php endif; ?>
          <?php if($r['directions']): ?><a class="text-link" href="<?php echo esc_url($r['directions']); ?>" target="_blank" rel="noopener noreferrer">Cómo llegar</a><?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
      <div class="locator-empty" data-store-empty <?php echo $visible?'hidden':''; ?>><h2>No encontramos esa sucursal</h2><p>Prueba otro nombre o limpia los filtros.</p></div>
    </div>
    <?php if(mct_design('show_store_map')): ?>
    <div class="locator-map" id="mapa-sucursal" tabindex="-1">
      <div class="locator-map-canvas">
        <iframe data-store-map title="<?php echo esc_attr('Mapa del centro comercial: '.($active?$active['post']->post_title:'Guatemala')); ?>" <?php echo $active&&$active['embed']?'src="'.esc_url($active['embed']).'"':'hidden'; ?> loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        <div class="map-empty" data-map-empty <?php echo $active&&$active['embed']?'hidden':''; ?>><?php echo mct_icon('sucursales'); ?><p>Selecciona una sucursal con dirección confirmada para ver su ubicación.</p></div>
      </div>
      <div class="locator-map-caption"><span class="eyebrow">TU PRÓXIMA VISITA</span><h2 data-map-title><?php echo esc_html($active?$active['post']->post_title:'Elige tu sucursal'); ?></h2><p data-map-address><?php echo esc_html($active?$active['address']:''); ?></p><a class="button" data-map-directions href="<?php echo esc_url($active?$active['directions']:''); ?>" target="_blank" rel="noopener noreferrer" <?php echo $active&&$active['directions']?'':'hidden'; ?>>Abrir indicaciones</a></div>
    </div>
    <?php endif; ?>
  </div>
</section>
<p class="locator-note"><?php echo esc_html(mct_design('locator_note')); ?></p>
<?php get_footer(); ?>
