<script setup>
/**
 * ==========================================
 * LÓGICA DEL SCRIPT (Perfil de Usuario)
 * ==========================================
 */
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Importación de componentes hijos (Widgets del perfil)
import XPBar from '@/Pages/Profile/XPBar.vue';
import WalletSummary from '@/Pages/Profile/WalletSummary.vue';
import AchievementsList from '@/Pages/Profile/AchievementsList.vue';
import FriendsList from '@/Pages/Profile/FriendsList.vue';
import RecentActivity from '@/Pages/Profile/RecentActivity.vue';

// Accedemos a los datos globales de la página (Inertia Shared Props)
const page = usePage();
// Extraemos el usuario autenticado. Si no carga, usamos un objeto vacío {} para evitar errores.
const user = page.props.auth.user || {};

// CÁLCULO DE XP (Experiencia)
// Calculamos el porcentaje de progreso para la barra de nivel.
// Math.min(100, ...) asegura que la barra nunca se pase del 100%.
const xpPercent = computed(() => {
  const xp = Number(user.xp ?? 0);
  const xpNext = Number(user.xp_next ?? 100);
  return xpNext > 0 ? Math.min(100, Math.round((xp / xpNext) * 100)) : 0;
});

// URL del Banner (Imagen de fondo superior)
const bannerUrl = '/images/banner.png';
</script>

<template>
  <Head title="Perfil" />

  <!-- 
    ==========================================
           CONTENEDOR PRINCIPAL
    ==========================================
    min-h-screen : Altura mínima de toda la pantalla.
    bg-[#071014] : Fondo muy oscuro (casi negro) específico del perfil.
    text-gray-100: Texto casi blanco para lectura cómoda.
  -->
  <div class="min-h-screen bg-[#071014] text-gray-100 pb-12">

    <!-- 
      ==========================================
             SECCIÓN 1: BANNER SUPERIOR
      ==========================================
      h-48 md:h-64 : Altura responsiva (más alto en PC).
      relative     : Necesario para posicionar el avatar "encima" del borde inferior.
      overflow-hidden : Recorta la imagen si se sale.
    -->
    <div class="relative h-48 md:h-56 lg:h-64 overflow-hidden">
      <!-- Imagen de fondo con opacidad reducida -->
      <img :src="bannerUrl" alt="banner" class="absolute inset-0 w-full h-full object-cover opacity-60" />
      <!-- Degradado oscuro encima de la imagen para mejorar la lectura del texto -->
      <div class="absolute inset-0 bg-black/50"></div>

      <!-- 
         CONTENEDOR DE INFORMACIÓN DE CABECERA
         Se alinea al fondo (items-end) para que el avatar "pise" la línea del banner.
      -->
      <div class="relative h-full flex items-end md:items-center px-6 md:px-12 lg:px-20 pb-6 md:pb-0">
        
        <!-- BLOQUE: AVATAR Y DATOS -->
        <div class="flex items-end md:items-center space-x-6">
          
          <!-- 
            IMAGEN DE AVATAR
            w-28 h-28     : Tamaño en móvil.
            md:w-36       : Tamaño en PC (más grande).
            ring-4        : Borde grueso alrededor del avatar.
            ring-black    : El borde es negro para separarlo del fondo.
            shadow-xl     : Sombra pronunciada para dar profundidad.
            avatar-neon   : Clase personalizada (ver <style>) para el brillo verde.
          -->
          <img
            :src="`/avatars/${user.avatar || 'avatar_default.png'}`"
            alt="avatar"
            class="w-28 h-28 md:w-36 md:h-36 rounded-xl object-cover ring-4 ring-offset-2 ring-black shadow-xl avatar-neon"
          />
          
          <!-- TEXTOS DEL USUARIO -->
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl md:text-3xl font-bold">{{ user.name }}</h1>
              <!-- Etiqueta de Rol (ej: Admin, VIP) -->
              <span class="px-2 py-0.5 rounded text-xs bg-[#0b1720] border border-gray-700 text-neon-green">
                {{ user.role || 'Usuario' }}
              </span>
            </div>

            <!-- Biografía (Itálica y gris suave) -->
            <p class="text-sm text-gray-300 mt-1 italic">
                {{ user.bio || 'Este jugador no escribió biografía todavía.' }}
            </p>

            <!-- Estadísticas rápidas y Botón de Editar -->
            <div class="mt-3 flex items-center gap-3">
              <div class="text-sm text-gray-200">Nivel <strong class="text-neon-green">{{ user.level ?? 1 }}</strong></div>
              <div class="text-sm text-gray-300">{{ user.xp ?? 0 }} XP</div>
              
              <Link href="/profile/edit" class="ml-4 inline-block px-3 py-1 rounded bg-neon-green text-black font-semibold hover:opacity-90 transition text-xs uppercase tracking-wider">
                Modificar perfil
              </Link>
            </div>
          </div>
        </div>

        <!-- ESTADO (Online/Offline) - Solo visible en PC (hidden md:flex) -->
        <div class="ml-auto hidden md:flex items-center gap-6">
          <div class="text-right">
            <div class="text-xs text-gray-300">Estado</div>
            <div :class="user.status === 'online' ? 'text-green-400' : 'text-gray-400'">
                {{ user.status === 'online' ? 'En línea' : 'Offline' }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 
      ==========================================
             SECCIÓN 2: TARJETA FLOTANTE (NIVEL)
      ==========================================
      -mt-0 : Margen negativo (ajustar si quieres que se solape con el banner).
      z-10  : Asegura que esté por encima de otros elementos.
    -->
    <div class="max-w-6xl mx-auto px-6 relative z-10 mt-6">
      <div class="bg-[#07121a] border border-gray-800 rounded-xl p-5 shadow-lg">
        <div class="md:flex md:items-center md:gap-6">
          
          <!-- LADO IZQUIERDO: BARRA DE PROGRESO -->
          <div class="flex-1">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-lg font-semibold text-neon-green">Progreso de nivel</h2>
                <p class="text-sm text-gray-300">Nivel {{ user.level ?? 1 }} · {{ user.xp ?? 0 }} / {{ user.xp_next ?? 100 }} XP</p>
              </div>
              <div class="hidden md:block">
                <button class="px-3 py-1 rounded bg-[#1f2b2f] text-sm border border-gray-700 hover:border-gray-500 transition">Ver logros</button>
              </div>
            </div>

            <div class="mt-3">
              <XPBar :percent="xpPercent" />
            </div>
          </div>

          <!-- LADO DERECHO: RESUMEN DE BILLETERA -->
          <div class="mt-4 md:mt-0 md:w-64">
            <WalletSummary :user="user" />
          </div>
        </div>
      </div>
    </div>

    <!-- 
      ==========================================
             SECCIÓN 3: GRID PRINCIPAL
      ==========================================
      grid-cols-1   : 1 columna en móvil.
      lg:grid-cols-3: 3 columnas en pantallas grandes.
      gap-6         : Espacio entre columnas.
    -->
    <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6 pb-12">

      <!-- COLUMNA IZQUIERDA (Widgets Pequeños) -->
      <div class="space-y-6 col-span-1">
        
        <!-- Componente de Logros -->
        <AchievementsList :user="user" />
        
        <!-- Widget: Estadísticas Rápidas -->
        <div class="bg-[#07121a] border border-gray-800 rounded-xl p-4">
          <h3 class="text-sm font-semibold text-neon-green mb-3">Acceso rápido</h3>
          <ul class="text-sm text-gray-300 space-y-2">
            <li>Insignias <span class="text-neon-green float-right"> {{ (user.badges ?? 0) }}</span></li>
            <li>Juegos <span class="text-neon-green float-right"> {{ (user.games ?? 0) }}</span></li>
            <li>Amigos <span class="text-neon-green float-right"> {{ (user.friends_count ?? 0) }}</span></li>
          </ul>
        </div>

        <!-- Componente de Amigos -->
        <FriendsList :friends="page.props.auth.friends || []" />
      </div>

      <!-- COLUMNA DERECHA (Contenido Principal - Más ancha) -->
      <div class="col-span-2 space-y-6">
        
        <!-- Actividad Reciente -->
        <RecentActivity :activities="page.props.auth.recent_activity || []" />
        
        <!-- Tarjeta: Juego Favorito -->
        <div class="bg-[#07121a] border border-gray-800 rounded-xl p-6">
          <h3 class="text-lg font-semibold text-neon-green mb-4">Juego favorito</h3>
          <div class="md:flex md:items-center md:gap-6">
            <div class="w-full md:w-48 h-32 bg-gray-800 rounded overflow-hidden relative group">
                <!-- Placeholder de imagen -->
                <div class="absolute inset-0 flex items-center justify-center text-gray-600 font-bold bg-black/40">GAME IMG</div>
            </div>
            <div class="mt-3 md:mt-0">
              <h4 class="font-bold">Lucky Clover</h4>
              <p class="text-sm text-gray-300 mt-1">27 horas jugadas · 6 logros</p>
              <div class="mt-3">
                <button class="px-3 py-1 rounded bg-neon-green text-black font-semibold hover:opacity-90 transition">Ver juego</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Caja de Comentarios -->
        <div class="bg-[#07121a] border border-gray-800 rounded-xl p-6">
          <h3 class="text-lg font-semibold text-neon-green mb-4">Comentarios</h3>
          <textarea 
            placeholder="Añadir un comentario..." 
            class="w-full bg-[#0b1216] border border-gray-800 p-3 rounded text-sm text-gray-100 resize-none h-32 focus:border-neon-green focus:ring-1 focus:ring-neon-green transition outline-none"
          ></textarea>
          <div class="mt-3 flex justify-end">
            <button class="px-4 py-2 rounded bg-neon-green text-black font-semibold hover:opacity-90 transition">Publicar</button>
          </div>
        </div>

      </div>
    </div>

    <!-- 
      ==========================================
               FIRMA PERSONAL (FOOTER)
      ==========================================
      Un detalle final elegante para cerrar la página.
    -->
    <div class="mt-12 mb-8 text-center opacity-30 hover:opacity-100 transition-opacity duration-500 cursor-default">
        <p class="text-xs text-gray-500 uppercase tracking-widest">
            Developed & Designed by
        </p>
        <p class="text-sm font-bold text-neon-green mt-1 font-mono">
            &lt; AngelV-Dev /&gt;
        </p>
    </div>

  </div>
</template>

<style scoped>
/* UTILIDADES CSS ESPECÍFICAS
   Aunque usamos Tailwind, a veces necesitamos variables CSS para efectos avanzados
   como el resplandor del avatar (glow).
*/
:root {
  --neon: #00ff66;
}

/* Clases auxiliares para usar la variable CSS si Tailwind falla */
.text-neon-green { color: var(--neon); }
.bg-neon-green { background: var(--neon); }

/* Efecto especial para el avatar: Resplandor verde */
.avatar-neon {
  box-shadow: 0 0 8px rgba(0,255,102,0.12), 0 0 20px rgba(0,255,102,0.08);
  transition: box-shadow .3s ease, transform .15s ease;
}

/* Animación al pasar el mouse sobre el avatar */
.avatar-neon:hover { 
    transform: translateY(-4px) scale(1.02); 
    box-shadow: 0 6px 30px rgba(0,255,102,0.18); 
}
</style>
<!-- 
==========================================
                   __
                  / _)
         _/\/\/\_/ /
       _|         /
     _|   ( |  ( |
    /__.-'|_|--|_| Tovar
 ==========================================
-->