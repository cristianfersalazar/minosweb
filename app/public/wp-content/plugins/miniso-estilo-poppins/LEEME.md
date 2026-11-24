# MINISO · Poppins y movimiento

Complemento visual para tu tema MINISO Catálogo. Requiere tener activo ese tema o un tema hijo que lo use como plantilla. No es un tema de reemplazo.

## Instalar

1. En WordPress, entra en Plugins → Añadir nuevo → Subir plugin.
2. Selecciona `MINISO_Poppins_Animaciones.zip` SIN descomprimir.
3. Pulsa Instalar ahora y Activar.
4. Visita la página pública. Si tenías esa pestaña abierta, recárgala con Ctrl + F5.
5. En Apariencia → Personalizar → MINISO · Poppins y movimiento puedes editar los dos colores y activar o desactivar el movimiento. Pulsa Publicar para guardar.

Conserva los complementos WooCommerce y MINISO Catálogo activos. Este añadido no sustituye ninguno de ellos.

## Qué cambia

- Poppins para textos, botones, menú y formularios de la página pública; no cambia la tipografía del panel de administración.
- Títulos principales rojos y títulos de beneficios azul petróleo, con un subrayado corto de color.
- Entradas suaves de texto una sola vez al entrar en pantalla, no animaciones continuas.
- Botones y tarjetas con una elevación pequeña al pasar el cursor en computadora.
- Subrayado en el menú y fondo suave en la opción móvil activa.
- Poppins se sirve desde tu propio WordPress: no hay solicitudes externas a Google Fonts y funciona en Local sin conexión después de instalar.
- Conserva las imágenes, las proporciones de los personajes, el logo y el texto LIFE IS FOR FUN incorporado en la fotografía.
- Respeta la preferencia de reducir movimiento del dispositivo. Si JavaScript falla o está desactivado, el contenido permanece visible y Poppins/color se mantienen.

El contenido de la página se sigue editando en Páginas, Productos y WooCommerce → MINISO Catálogo.

## Dar color o movimiento a un bloque concreto

Al editar Lover+ o Ayuda, selecciona un bloque de párrafo o encabezado y abre sus opciones Avanzado → Clase(s) CSS adicional(es). Puedes escribir:

- `miniso-texto-rojo`: color principal.
- `miniso-texto-azul`: color secundario.
- `miniso-texto-rosa`: rosa oscuro.
- `miniso-animar`: entrada suave una vez al desplazarte.

Se pueden combinar con un espacio, por ejemplo: `miniso-texto-azul miniso-animar`.
Los colores de bloques establecidos individualmente en el editor pueden tener prioridad. Para usar estas clases, deja el color del bloque en predeterminado.

## Ajustar el código

`assets/poppins.css` contiene los colores, fuente, estilos y comentarios EDITAR/FUNCIÓN. `assets/motion.js` contiene la entrada de texto, con duración de 550 ms y desplazamiento de 12 px.

Para cambios personales que quieras conservar al actualizar el complemento, usa Apariencia → Personalizar → CSS adicional. Por ejemplo:

```css
/* Cambia el rosa opcional sin modificar los archivos del complemento. */
body.miniso-poppins { --mp-rose: #a82b69; }
```

Desactivar este complemento retira sus estilos y movimientos; no borra productos, páginas, portadas ni ajustes del catálogo. Los colores elegidos se guardan como ajustes del tema.

## Referencia y licencia

Propuesta propia inspirada en la organización por colecciones de https://minisoshop.co.uk/ y en tus materiales. La web de referencia mostró una verificación automática, por lo que no se ha copiado ni verificado su animación exacta.

Poppins: Copyright 2020 The Poppins Project Authors, https://github.com/itfoundry/Poppins. Distribuida bajo SIL Open Font License 1.1; véase `assets/fonts/OFL.txt`. Fuentes obtenidas de https://github.com/google/fonts/tree/main/ofl/poppins y convertidas sin eliminar glifos a WOFF. Código del complemento: GPL-2.0-or-later.

La legibilidad de colores personalizados depende de tu elección: los valores iniciales se eligieron para texto sobre fondos claros. Los textos y dibujos incluidos dentro de una imagen no cambian de fuente con CSS.
