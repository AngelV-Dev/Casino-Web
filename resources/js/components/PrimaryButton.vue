<template>
    <!-- 
      ==========================================
         COMPONENTE: BOTÓN PRIMARIO MAESTRO
      ==========================================
      Este es el botón base reutilizable. Cualquier cambio aquí
      se reflejará automáticamente en el Login, Register y Dashboard.
    -->
    <button
        class="
            /* === DISPOSICIÓN Y FORMA === */
            inline-flex items-center      /* Alinea el texto e iconos (si hubiera) al centro verticalmente */
            rounded-full                  /* CAMBIO CLAVE: Hace que el botón sea una píldora perfecta (círculo en los bordes) */
            border border-transparent     /* Define un borde transparente para mantener el tamaño sin línea visible */
            px-4 py-2                     /* Espaciado interno (Padding): 4 horizontal, 2 vertical */

            /* === COLORES Y FONDO === */
            bg-neon-green                 /* Color base: Verde Neón (Definido en tu tailwind.config.js) */
            
            /* === TIPOGRAFÍA === */
            text-xs font-bold             /* Texto pequeño (xs) pero grueso (bold) para legibilidad */
            uppercase tracking-widest     /* Texto en MAYÚSCULAS con letras muy separadas (estilo moderno/tech) */
            text-vertical-text-gray       /* Color del texto: Gris oscuro (Definido en config) para contraste con el neón */

            /* === ANIMACIONES Y ESTADOS === */
            transition duration-150 ease-in-out  /* Suaviza todos los cambios de color (0.15 segundos) */
            
            /* Estado: Hover (Pasar el mouse por encima) */
            hover:bg-darker-green         /* Se oscurece a un verde más serio */

            /* Estado: Focus (Navegación con teclado/Tab) */
            focus:bg-gray-700             /* Cambia a gris si se selecciona con teclado */
            focus:outline-none            /* Elimina el borde azul feo por defecto del navegador */
            focus:ring-2                  /* Agrega un anillo exterior de selección */
            focus:ring-indigo-500         /* Color del anillo */
            focus:ring-offset-2           /* Separación entre el botón y el anillo */

            /* Estado: Active (Mientras se hace clic) */
            active:bg-gray-900            /* Se pone casi negro al presionar */
        "
    >
        <!-- 
           SLOT: El hueco mágico
           Aquí es donde Vue inyectará el texto que escribas al usar el componente.
           Ejemplo: <PrimaryButton>ESTO VA AQUÍ</PrimaryButton>
        -->
        <slot />
    </button>
</template>