<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const page = usePage();
const user = page.props.auth.user || {};

// Cálculo de XP
const xpPercent = computed(() => {
  const xp = Number(user.xp ?? 0);
  const xpNext = Number(user.xp_next ?? 100);
  return xpNext > 0 ? Math.min(100, Math.round((xp / xpNext) * 100)) : 0;
});

// Estadísticas del usuario
const stats = [
  { label: 'Nivel', value: user.level || 1, icon: '⭐' },
  { label: 'Partidas', value: user.games || 0, icon: '🎮' },
  { label: 'Victorias', value: user.wins || 0, icon: '🏆' },
  { label: 'Racha', value: user.streak || 0, icon: '🔥' }
];

// Amigos y actividad reciente
const friends = page.props.friends || [];
const activities = page.props.recent_activity || [];

// Forzar que los estilos se carguen completamente
onMounted(() => {
  document.body.classList.add('profile-loaded');
});
</script>

<template>
  <Head title="Mi Perfil" />
  
  <AppLayout>
    <div class="min-h-screen bg-[#0a0b0d] pb-16">
      
      <!-- BANNER SUPERIOR -->
      <div class="relative h-64 md:h-80 overflow-hidden">
        <!-- Banner personalizable -->
        <div class="absolute inset-0 bg-gradient-to-b from-[#1a1d24] to-[#0a0b0d]">
          <img 
            v-if="user.banner"
            :src="`/banners/${user.banner}`" 
            class="w-full h-full object-cover opacity-30"
          />
          <div v-else class="absolute inset-0 bg-[url('/images/derecha.png')] bg-cover bg-center opacity-10"></div>
        </div>
        
        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0b0d] via-transparent to-transparent"></div>

        <div class="relative h-full max-w-7xl mx-auto px-6 md:px-12 flex items-end pb-6">
          <div class="w-full"> 
            <div class="flex flex-col md:flex-row items-center md:items-center gap-6">
              
              <div class="relative group flex-shrink-0">
                <div 
                  class="absolute -inset-1 rounded-2xl blur opacity-75 group-hover:opacity-100 transition bg-lime-500"
                ></div>
                <img
                  :src="`/avatars/${user.avatar || 'avatar_default.png'}`"
                  class="relative w-32 h-32 md:w-40 md:h-40 rounded-2xl object-cover border-4 border-lime-500 shadow-2xl"
                />
                <Link 
                  href="/profile/edit" 
                  class="absolute bottom-2 right-2 bg-lime-500 hover:bg-lime-400 text-black font-bold p-2.5 rounded-lg shadow-lg transition transform hover:scale-110 z-10"
                >
                  <i class="fas fa-edit text-sm"></i>
                </Link>
              </div>

              <div class="flex-1 text-center md:text-left w-full">
                <div class="flex flex-col md:flex-row md:items-center gap-3 mb-2 justify-center md:justify-start">
                  <h1 class="text-4xl md:text-5xl font-black text-white drop-shadow-lg">
                    {{ user.name }}
                  </h1>
                  <span 
                    class="px-4 py-1.5 rounded-full text-black text-sm font-bold uppercase tracking-wider shadow-lg inline-block bg-lime-500"
                  >
                    {{ user.role || 'Usuario' }}
                  </span>
                </div>

                <p class="text-gray-400 text-base mb-2 text-left md:text-left">
                  {{ user.email }}
                </p>

                <p class="text-white text-base md:text-lg font-medium mb-4 text-left md:text-left">
                  {{ user.bio || '¡Conquistando el casino uno a uno! 🎰' }}
                </p>

                <div class="flex flex-wrap justify-center md:justify-start gap-4 text-sm">
                  <div class="flex items-center gap-2 bg-black/40 px-4 py-2 rounded-lg border border-white/20">
                    <span class="text-lime-400 font-bold">Nivel {{ user.level || 1 }}</span>
                  </div>
                  <div class="flex items-center gap-2 bg-black/40 px-4 py-2 rounded-lg border border-white/20">
                    <span class="text-gray-300">{{ user.xp || 0 }} XP</span>
                  </div>
                  <div class="flex items-center gap-2 bg-black/40 px-4 py-2 rounded-lg border border-white/20">
                    <span :class="user.status === 'online' ? 'text-green-400' : 'text-gray-400'">
                      <i class="fas fa-circle text-xs mr-1"></i>
                      {{ user.status === 'online' ? 'En línea' : 'Offline' }}
                    </span>
                  </div>
                </div>
              </div>

              <Link 
                href="/profile/edit"
                class="hidden md:flex items-center gap-2 px-6 py-3 bg-lime-500 hover:bg-lime-400 text-black font-bold rounded-xl shadow-lg transition transform hover:scale-105"
              >
                <i class="fas fa-user-edit"></i>
                <span>Editar Perfil</span>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-6 mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1 space-y-6">
          
          <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-lime-500">Progreso de Nivel</h3>
              <span class="text-2xl">⭐</span>
            </div>
            
            <div class="flex items-center justify-between mb-2">
              <span class="text-white font-bold text-lg">Nivel {{ user.level || 1 }}</span>
              <span class="text-gray-400 text-sm">{{ user.xp || 0 }} / {{ user.xp_next || 100 }} XP</span>
            </div>
            
            <div class="w-full bg-gray-800 h-4 rounded-full overflow-hidden mb-4">
              <div 
                class="h-full bg-gradient-to-r from-lime-500 to-green-600 transition-all duration-700 shadow-lg"
                :style="{ width: xpPercent + '%' }"
              ></div>
            </div>

            <p class="text-xs text-gray-400 text-center">
              {{ 100 - xpPercent }}% para el siguiente nivel
            </p>
          </div>

          <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-lime-500 mb-4 flex items-center gap-2">
              <i class="fas fa-chart-line"></i>
              Estadísticas
            </h3>
            
            <div class="grid grid-cols-2 gap-4">
              <div v-for="stat in stats" :key="stat.label" class="bg-black/40 rounded-xl p-4 text-center border border-gray-800 hover:border-lime-500/50 transition">
                <div class="text-3xl mb-2">{{ stat.icon }}</div>
                <div class="text-2xl font-bold text-white mb-1">{{ stat.value }}</div>
                <div class="text-xs text-gray-400 uppercase tracking-wide">{{ stat.label }}</div>
              </div>
            </div>
          </div>

          <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-lime-500 mb-4 flex items-center gap-2">
              <i class="fas fa-users"></i>
              Amigos ({{ friends.length }})
            </h3>
            
            <div v-if="friends.length > 0" class="space-y-3">
              <div 
                v-for="friend in friends.slice(0, 5)" 
                :key="friend.id"
                class="flex items-center gap-3 p-3 bg-black/40 rounded-lg border border-gray-800 hover:border-lime-500/50 transition"
              >
                <img 
                  :src="friend.avatar || '/avatars/avatar_default.png'" 
                  class="w-12 h-12 rounded-lg object-cover"
                />
                <div class="flex-1">
                  <div class="text-white font-semibold text-sm">{{ friend.name }}</div>
                  <div class="text-xs text-gray-400">{{ friend.last_online || 'Hace días' }}</div>
                </div>
                <span v-if="friend.level" class="text-xs text-lime-400 font-bold">
                  Nv. {{ friend.level }}
                </span>
              </div>
            </div>
            
            <div v-else class="text-center py-8 text-gray-500">
              <i class="fas fa-user-friends text-4xl mb-3 opacity-30"></i>
              <p class="text-sm">No hay amigos todavía</p>
            </div>
          </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
          
          <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl">
            <h3 class="text-xl font-bold text-lime-500 mb-6 flex items-center gap-2">
              <i class="fas fa-history"></i>
              Actividad Reciente
            </h3>
            
            <div v-if="activities.length > 0" class="space-y-4">
              <div 
                v-for="activity in activities" 
                :key="activity.id"
                class="flex items-start gap-4 p-4 bg-black/40 rounded-xl border border-gray-800 hover:border-lime-500/50 transition"
              >
                <div class="w-20 h-16 bg-gradient-to-br from-lime-500/20 to-green-600/20 rounded-lg flex items-center justify-center flex-shrink-0">
                  <i class="fas fa-gamepad text-lime-400 text-2xl"></i>
                </div>
                <div class="flex-1">
                  <h4 class="text-white font-bold mb-1">{{ activity.title }}</h4>
                  <p class="text-gray-400 text-sm">{{ activity.subtitle }}</p>
                </div>
                <span class="text-xs text-gray-500">{{ activity.time }}</span>
              </div>
            </div>
            
            <div v-else class="text-center py-12 text-gray-500">
              <i class="fas fa-inbox text-5xl mb-4 opacity-30"></i>
              <p class="text-sm">No hay actividad reciente</p>
              <Link href="/dashboard" class="inline-block mt-4 px-4 py-2 bg-lime-500 hover:bg-lime-400 text-black font-bold rounded-lg transition">
                ¡Empieza a jugar!
              </Link>
            </div>
          </div>

          <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl">
            <h3 class="text-xl font-bold text-lime-500 mb-6 flex items-center gap-2">
              <i class="fas fa-star"></i>
              Juego Favorito
            </h3>
            
            <div class="md:flex items-center gap-6">
              <div class="w-full md:w-64 h-40 bg-gradient-to-br from-purple-900/40 to-lime-900/40 rounded-xl overflow-hidden relative group mb-4 md:mb-0">
                <div class="absolute inset-0 flex items-center justify-center">
                  <i class="fas fa-gamepad text-6xl text-gray-600"></i>
                </div>
                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                  <button class="px-4 py-2 bg-lime-500 hover:bg-lime-400 text-black font-bold rounded-lg transition">
                    Ver Juego
                  </button>
                </div>
              </div>
              
              <div class="flex-1">
                <h4 class="text-2xl font-bold text-white mb-2">Lucky Clover 🍀</h4>
                <p class="text-gray-400 mb-4">27 horas jugadas · 6 logros desbloqueados</p>
                <div class="flex gap-3">
                  <Link href="/dashboard" class="px-5 py-2 bg-gradient-to-r from-lime-500 to-green-600 hover:from-lime-400 hover:to-green-500 text-black font-bold rounded-lg transition transform hover:scale-105">
                    Jugar Ahora
                  </Link>
                  <button class="px-5 py-2 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-lg transition">
                    Ver Stats
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl">
            <h3 class="text-xl font-bold text-lime-500 mb-6 flex items-center gap-2">
              <i class="fas fa-trophy"></i>
              Logros Desbloqueados
            </h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div v-for="i in 8" :key="i" class="aspect-square bg-black/40 rounded-xl border border-gray-800 hover:border-lime-500/50 transition flex items-center justify-center group cursor-pointer">
                <div class="text-center">
                  <i class="fas fa-lock text-3xl text-gray-600 group-hover:text-gray-500 transition"></i>
                  <p class="text-xs text-gray-500 mt-2">Bloqueado</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-16 text-center opacity-40 hover:opacity-100 transition-opacity duration-500 cursor-default">
        <p class="text-xs text-gray-500 uppercase tracking-widest">Diseñado por</p>
        <p class="text-sm font-bold text-lime-500 mt-1 font-mono">&lt; iTovarr /&gt;</p>
      </div>

    </div>
  </AppLayout>
</template>

<style scoped>
* {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

@keyframes shimmer {
  0% { background-position: -1000px 0; }
  100% { background-position: 1000px 0; }
}

.animate-shimmer {
  animation: shimmer 2s infinite;
  background: linear-gradient(to right, transparent 0%, rgba(255,255,255,0.1) 50%, transparent 100%);
  background-size: 1000px 100%;
}

.transition {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>