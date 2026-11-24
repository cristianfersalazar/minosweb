(function(wp) {
  'use strict';
  if (!wp || !wp.blocks || !wp.element || !wp.blockEditor || !wp.components) return;
  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var MediaUpload = wp.blockEditor.MediaUpload;
  var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
  var TextControl = wp.components.TextControl;
  var TextareaControl = wp.components.TextareaControl;
  var SelectControl = wp.components.SelectControl;
  var Button = wp.components.Button;
  var Notice = wp.components.Notice;

  var KIND = [
    {label:'Contenido',value:'contenido'},
    {label:'Tarjetas',value:'tarjetas'},
    {label:'Galería de imágenes',value:'galeria'},
    {label:'Premios o beneficios',value:'premios'},
    {label:'Videos',value:'videos'},
    {label:'Preguntas desplegables',value:'preguntas'},
    {label:'Destacados',value:'destacado'}
  ];
  var COLORS = [
    {label:'Rosa pastel',value:'rosa'},
    {label:'Amarillo vivo',value:'amarillo'},
    {label:'Azul pastel',value:'azul'},
    {label:'Verde pastel',value:'verde'},
    {label:'Lila pastel',value:'lila'},
    {label:'Blanco',value:'blanco'},
    {label:'Rojo MINISO',value:'rojo'},
    {label:'Elegir color',value:'propio'}
  ];
  function Field(label, value, update, isLong) {
    return el(isLong ? TextareaControl : TextControl, {
      label: label, value: value || '', onChange:update,
      rows:isLong?4:undefined,
      __nextHasNoMarginBottom:true
    });
  }
  registerBlockType('miniso/seccion', {
    title:'MINISO · Sección personalizable',
    icon:'layout',
    category:'design',
    description:'Sección reutilizable, sin contenido precargado, para cualquier página y novedad de MINISO.',
    attributes: {
      kind:{type:'string',default:'contenido'}, background:{type:'string',default:'rosa'},
      customColor:{type:'string',default:''}, heading:{type:'string',default:''},
      intro:{type:'string',default:''}, body:{type:'string',default:''},
      items:{type:'array',default:[]}, columns:{type:'number',default:3},
      position:{type:'string',default:'after'}
    },
    supports:{html:false,anchor:true,align:false,multiple:true},
    edit:function(props) {
      var a = props.attributes;
      var items = Array.isArray(a.items) ? a.items : [];
      var set = props.setAttributes;
      var palette = {rosa:'#FFE1E8',amarillo:'#FFE17A',azul:'#CFEFFF',verde:'#CFF4D8',lila:'#E8D9FF',blanco:'#FFFFFF',rojo:'#E60020'};
      var bg = a.background === 'propio' ? (a.customColor || '#ffffff') : (palette[a.background] || '#ffffff');
      var textColor = a.background === 'rojo' ? '#ffffff' : '#171717';
      function changeItem(i,key,value){
        var copy=items.map(function(x){return Object.assign({},x);});
        copy[i][key]=value;set({items:copy});
      }
      function itemForm(item,i){
        return el('div',{key:i,style:{background:'#fff',padding:18,border:'1px solid #dadce0',borderRadius:12,margin:'14px 0'}},
          el('div',{style:{display:'flex',alignItems:'center',justifyContent:'space-between',gap:10,marginBottom:12}},
            el('strong',{},'Elemento '+(i+1)),
            el(Button,{variant:'tertiary',isDestructive:true,onClick:function(){set({items:items.filter(function(x,j){return i!==j;})});}},'Eliminar')
          ),
          a.kind==='premios' ? Field('Cantidad o dato',item.quantity,function(v){changeItem(i,'quantity',v);}) : null,
          a.kind==='preguntas' ? Field('Pregunta',item.title,function(v){changeItem(i,'title',v);}) : Field('Título',item.title,function(v){changeItem(i,'title',v);}),
          Field(a.kind==='preguntas'?'Respuesta':'Descripción',item.text,function(v){changeItem(i,'text',v);},true),
          a.kind==='videos' ? Field('Enlace de video',item.videoUrl,function(v){changeItem(i,'videoUrl',v);}) : null,
          a.kind!=='preguntas' ? el('div',{},
            el(MediaUploadCheck,{},
              el(MediaUpload,{
                allowedTypes:['image'],value:item.imageId||0,
                onSelect:function(media){changeItem(i,'imageId',media.id||0);},
                render:function(m){return el('div',{style:{display:'flex',alignItems:'center',gap:10,marginBottom:12}},
                  el(Button,{variant:'secondary',onClick:m.open},item.imageId?'Cambiar imagen':'Elegir imagen'),
                  item.imageId ? el(Button,{variant:'tertiary',onClick:function(){changeItem(i,'imageId',0);}},'Quitar imagen') : null
                );}
              })
            ),
            Field('Enlace opcional',item.url,function(v){changeItem(i,'url',v);}),
            Field('Texto del enlace',item.linkText,function(v){changeItem(i,'linkText',v);})
          ) : null,
          el('div',{style:{display:'flex',gap:8}},
            el(Button,{variant:'secondary',disabled:i===0,onClick:function(){var x=items.slice();var t=x[i];x[i]=x[i-1];x[i-1]=t;set({items:x});}},'↑'),
            el(Button,{variant:'secondary',disabled:i===items.length-1,onClick:function(){var x=items.slice();var t=x[i];x[i]=x[i+1];x[i+1]=t;set({items:x});}},'↓')
          )
        );
      }
      return el('div', Object.assign({},useBlockProps({className:'miniso-section-editor',style:{border:'2px solid #e60020',borderRadius:14,overflow:'hidden',margin:'24px 0'}})),
        el('div',{style:{background:'#e60020',color:'#fff',padding:'12px 18px',fontWeight:700}},'MINISO · Sección personalizable'),
        el('div',{style:{background:bg,color:textColor,padding:'22px 24px'}},
          el('div',{style:{display:'grid',gridTemplateColumns:'repeat(auto-fit,minmax(190px,1fr))',gap:14,marginBottom:18}},
            el(SelectControl,{label:'Tipo de sección',value:a.kind,options:KIND,onChange:function(v){set({kind:v});}}),
            el(SelectControl,{label:'Color de fondo',value:a.background,options:COLORS,onChange:function(v){set({background:v});}}),
            el(SelectControl,{label:'Columnas',value:String(a.columns),options:[1,2,3,4].map(function(n){return {label:String(n),value:String(n)};}),onChange:function(v){set({columns:parseInt(v,10)});}})
          ),
          a.background==='propio' ? el(TextControl,{label:'Color hexadecimal',type:'color',value:a.customColor||'#ffffff',onChange:function(v){set({customColor:v});}}) : null,
          Field('Título (opcional)',a.heading,function(v){set({heading:v});}),
          Field('Introducción (opcional)',a.intro,function(v){set({intro:v});},true),
          Field('Contenido adicional (opcional)',a.body,function(v){set({body:v});},true),
          a.kind !== 'contenido' ? el('div',{},
            el('h4',{style:{fontSize:16,margin:'20px 0 8px'}},'Elementos de la sección'),
            items.map(itemForm),
            el(Button,{variant:'primary',onClick:function(){set({items:items.concat([{title:'',text:'',quantity:'',url:'',linkText:'',imageId:0,videoUrl:''}])});}},'+ Añadir elemento')
          ) : null,
          el('p',{style:{fontSize:13,opacity:.78,margin:'14px 0 0'}},'Los campos vacíos no se muestran al publicar. Puedes añadir tantas secciones y elementos como necesites.'),
          el('div',{style:{marginTop:16}},
            el(SelectControl,{label:'Ubicación en páginas con contenido automático',value:a.position||'after',options:[{label:'Antes del listado',value:'before'},{label:'Después del listado',value:'after'}],onChange:function(v){set({position:v});}})
          )
        )
      );
    },
    save:function(){return null;}
  });
})(window.wp);
