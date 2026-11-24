(function (wp) {
  'use strict';
  if (!wp || !wp.blocks || !wp.element || !wp.components || !wp.blockEditor) return;
  const el = wp.element.createElement;
  const reg = wp.blocks.registerBlockType;
  const b = wp.components;
  const useBlockProps = wp.blockEditor.useBlockProps;
  const MediaUpload = wp.blockEditor.MediaUpload;
  const MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
  const KIND = [
    {value:'banners',label:'Banners del carrusel'},
    {value:'colecciones',label:'Colecciones publicadas'},
    {value:'novedades',label:'Novedades publicadas'},
    {value:'sucursales',label:'Sucursales registradas'},
    {value:'lover',label:'Sección con imagen y texto'},
    {value:'video',label:'Video de Instagram, YouTube o MP4'},
    {value:'redes',label:'Redes sociales'}
  ];
  const COLORS = [
    {value:'blanco',label:'Blanco'}, {value:'rosa',label:'Rosa pastel'},
    {value:'amarillo',label:'Amarillo pastel'}, {value:'verde',label:'Verde pastel'},
    {value:'azul',label:'Azul pastel'}, {value:'lila',label:'Lila pastel'},
    {value:'rojo',label:'Rojo MINISO'}, {value:'oscuro',label:'Oscuro'},
    {value:'propio',label:'Color personalizado'}
  ];
  const PREVIEW_COLOR = {blanco:'#fff',rosa:'#ffe1e8',amarillo:'#ffe17a',verde:'#cff4d8',azul:'#cfefff',lila:'#e8d9ff',rojo:'#e60020',oscuro:'#202020'};
  const TYPES_WITH_LIST = ['colecciones','novedades','sucursales'];
  function field(name, value, cb, textarea) {
    return el(textarea ? b.TextareaControl : b.TextControl, {
      label:name, value:value || '', onChange:cb, rows:textarea?3:undefined,
      __nextHasNoMarginBottom:true
    });
  }
  reg('miniso/modulo', {
    title:'MINISO · Módulo conectado',
    icon:'screenoptions',
    category:'design',
    description:'Muestra banners, colecciones, novedades, sucursales, videos o redes. Todos los controles editan la presentación real.',
    attributes:{
      kind:{type:'string',default:'novedades'},position:{type:'string',default:'after'},enabled:{type:'boolean',default:true},
      eyebrow:{type:'string',default:''},heading:{type:'string',default:''},description:{type:'string',default:''},
      buttonText:{type:'string',default:''},buttonUrl:{type:'string',default:''},
      background:{type:'string',default:'blanco'},customColor:{type:'string',default:''},
      limit:{type:'number',default:6},columns:{type:'number',default:3},collectionLayout:{type:'string',default:'carousel'},collectionGroup:{type:'string',default:'licencias'},
      autoplay:{type:'boolean',default:true},interval:{type:'number',default:5},showCaptions:{type:'boolean',default:true},
      imageId:{type:'number',default:0},videoUrl:{type:'string',default:''},
      instagramUrl:{type:'string',default:''},facebookUrl:{type:'string',default:''},tiktokUrl:{type:'string',default:''}
    },
    supports:{html:false,anchor:true,multiple:true},
    edit:function(props){
      const a=props.attributes;
      const set=(name,value)=>props.setAttributes({[name]:value});
      const color = a.background==='propio' ? a.customColor||'#ffffff' : PREVIEW_COLOR[a.background]||'#fff';
      const dark = ['rojo','oscuro'].indexOf(a.background)>=0;
      const info = {
        banners:'Usa los banners publicados en WooCommerce → Banners MINISO.',
        colecciones:'Usa las colecciones existentes del catálogo. Puedes elegir carrusel horizontal o cuadrícula; la cantidad y las columnas se editan aquí.',
        novedades:'Usa las noticias publicadas en WooCommerce → Novedades MINISO.',
        sucursales:'Usa las sucursales publicadas y sus direcciones y horarios.',
        lover:'Solo mostrará los campos que escribas o la imagen que selecciones.',
        video:'Introduce un enlace válido; sin enlace el módulo no aparece.',
        redes:'Usa las cuentas del sitio o, si lo prefieres, los enlaces definidos aquí.'
      }[a.kind];
      return el('div',Object.assign({},useBlockProps({className:'miniso-module-editor',style:{border:'2px solid #e60020',borderRadius:15,margin:'20px 0',overflow:'hidden',background:'#fff'}})),
        el('div',{style:{background:'#e60020',color:'#fff',padding:'12px 16px',fontWeight:700}},'MINISO · Módulo conectado'),
        el('div',{style:{padding:20,background:color,color:dark?'#fff':'#181818'}},
          el(b.ToggleControl,{label:'Mostrar este módulo',checked:a.enabled,onChange:v=>set('enabled',v)}),
          el(b.SelectControl,{label:'Contenido conectado',value:a.kind,options:KIND,onChange:v=>set('kind',v)}),
          el('p',{style:{fontSize:13,margin:'2px 0 18px'}},info),
          el(b.SelectControl,{label:'Color de fondo',value:a.background,options:COLORS,onChange:v=>set('background',v)}),
          a.background==='propio' ? el(b.TextControl,{type:'color',label:'Color del fondo',value:a.customColor||'#ffffff',onChange:v=>set('customColor',v)}) : null,
          field('Texto pequeño superior (opcional)',a.eyebrow,v=>set('eyebrow',v)),
          field('Título (opcional)',a.heading,v=>set('heading',v)),
          field('Descripción (opcional)',a.description,v=>set('description',v),true),
          a.kind!=='banners' ? el('div',{},field('Texto del enlace o botón (opcional)',a.buttonText,v=>set('buttonText',v)),field('URL del botón (opcional)',a.buttonUrl,v=>set('buttonUrl',v))) : null,
          TYPES_WITH_LIST.includes(a.kind) ? el('div',{style:{display:'flex',gap:15,flexWrap:'wrap'}},
            el(b.RangeControl,{label:'Máximo de elementos',min:1,max:30,value:a.limit,onChange:v=>set('limit',v)}),
            el(b.SelectControl,{label:'Columnas',value:String(a.columns),options:[1,2,3,4].map(n=>({label:String(n),value:String(n)})),onChange:v=>set('columns',Number(v))})
          ) : null,
          a.kind==='colecciones' ? el(b.SelectControl,{label:'Tipo de contenido del catálogo',value:a.collectionGroup||'licencias',options:[{value:'licencias',label:'Licencias'},{value:'promociones',label:'Promociones'},{value:'temporadas',label:'Temporadas'}],onChange:v=>set('collectionGroup',v)}) : null,
          a.kind==='colecciones' ? el(b.SelectControl,{label:'Presentación de catálogo',value:a.collectionLayout||'carousel',options:[{value:'carousel',label:'Carrusel horizontal (como el Inicio original)'},{value:'grid',label:'Cuadrícula de tarjetas'}],onChange:v=>set('collectionLayout',v)}) : null,
          a.kind==='banners' ? el('div',{},
            el(b.ToggleControl,{label:'Cambio automático',checked:a.autoplay,onChange:v=>set('autoplay',v)}),
            el(b.RangeControl,{label:'Segundos entre banners',min:2,max:20,value:a.interval,onChange:v=>set('interval',v)}),
            el(b.ToggleControl,{label:'Mostrar títulos y botones de cada banner',checked:a.showCaptions,onChange:v=>set('showCaptions',v)})
          ) : null,
          a.kind==='lover' ? el(MediaUploadCheck,{},el(MediaUpload,{
            allowedTypes:['image'],value:a.imageId||0,onSelect:media=>set('imageId',media.id||0),
            render:({open})=>el('div',{style:{display:'flex',gap:10,alignItems:'center',margin:'14px 0'}},
              el(b.Button,{variant:'secondary',onClick:open},a.imageId?'Cambiar imagen':'Seleccionar imagen'),
              a.imageId?el(b.Button,{isDestructive:true,variant:'tertiary',onClick:()=>set('imageId',0)},'Quitar'):null)
          })) : null,
          a.kind==='video' ? field('URL de video (Instagram, YouTube, MP4, WebM)',a.videoUrl,v=>set('videoUrl',v)) : null,
          a.kind==='redes' ? el('div',{},
            field('Instagram (opcional)',a.instagramUrl,v=>set('instagramUrl',v)),
            field('Facebook (opcional)',a.facebookUrl,v=>set('facebookUrl',v)),
            field('TikTok (opcional)',a.tiktokUrl,v=>set('tiktokUrl',v))
          ) : null,
          el(b.SelectControl,{label:'Ubicación en páginas con listado automático',value:a.position||'after',options:[{value:'before',label:'Antes del listado'},{value:'after',label:'Después del listado'}],onChange:v=>set('position',v)}),
          el('p',{style:{fontSize:12,marginTop:16,opacity:.8}},'No se crean textos ni imágenes de muestra. Los campos vacíos quedan ocultos. Puedes cambiar el orden arrastrando este bloque desde la Vista de lista.')
        )
      );
    },
    save:()=>null
  });
})(window.wp);
