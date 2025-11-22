<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import XPBar from '@/Pages/Profile/XPBar.vue';
import WalletSummary from '@/Pages/Profile/WalletSummary.vue';
import AchievementsList from '@/Pages/Profile/AchievementsList.vue';
import FriendsList from '@/Pages/Profile/FriendsList.vue';
import RecentActivity from '@/Pages/Profile/RecentActivity.vue';

import { computed } from 'vue';

const page = usePage();
const user = page.props.auth.user || {};

// xp percentage computed
const xpPercent = computed(() => {
  const xp = Number(user.xp ?? 0);
  const xpNext = Number(user.xp_next ?? 100);
  return xpNext > 0 ? Math.min(100, Math.round((xp / xpNext) * 100)) : 0;
});

// banner image: use the uploaded image path (local path included)
const bannerUrl = '/images/banner.png';
</script>

<template>
  <Head title="Perfil" />

  <div class="min-h-screen bg-[#071014] text-gray-100">

    <!-- Banner -->
    <div class="relative h-48 md:h-56 lg:h-64 overflow-hidden">
      <img :src="bannerUrl" alt="banner" class="absolute inset-0 w-full h-full object-cover opacity-60" />
      <div class="absolute inset-0 bg-black/50"></div>

      <div class="relative h-full flex items-end md:items-center px-6 md:px-12 lg:px-20 pb-6 md:pb-0">
        <!-- Avatar -->
        <div class="flex items-end md:items-center space-x-6">
          <img
            :src="`/avatars/${user.avatar || 'avatar_default.png'}`"
            alt="avatar"
            class="w-28 h-28 md:w-36 md:h-36 rounded-xl object-cover ring-4 ring-offset-2 ring-black shadow-xl avatar-neon"
          />
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl md:text-3xl font-bold">{{ user.name }}</h1>
              <span class="px-2 py-0.5 rounded text-xs bg-[#0b1720] border border-gray-700 text-neon-green">{{ user.role || 'Usuario' }}</span>
            </div>

            <p class="text-sm text-gray-300 mt-1 italic">{{ user.bio || 'Este jugador no escribió biografía todavía.' }}</p>

            <div class="mt-3 flex items-center gap-3">
              <div class="text-sm text-gray-200">Nivel <strong class="text-neon-green">{{ user.level ?? 1 }}</strong></div>
              <div class="text-sm text-gray-300">{{ user.xp ?? 0 }} XP</div>
              <Link href="/profile/edit" class="ml-4 inline-block px-3 py-1 rounded bg-neon-green text-black font-semibold hover:opacity-90 transition">
                Modificar perfil
              </Link>
            </div>
          </div>
        </div>

        <!-- optional quick stats at right -->
        <div class="ml-auto hidden md:flex items-center gap-6">
          <div class="text-right">
            <div class="text-xs text-gray-300">Estado</div>
            <div :class="user.status === 'online' ? 'text-green-400' : 'text-gray-400'">{{ user.status === 'online' ? 'En línea' : 'Offline' }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Level card (overlapping) -->
    <div class="max-w-6xl mx-auto -mt-0 px-6 relative z-10">
      <div class="bg-[#07121a] border border-gray-800 rounded-xl p-5 shadow-lg">
        <div class="md:flex md:items-center md:gap-6">
          <div class="flex-1">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-lg font-semibold text-neon-green">Progreso de nivel</h2>
                <p class="text-sm text-gray-300">Nivel {{ user.level ?? 1 }} · {{ user.xp ?? 0 }} / {{ user.xp_next ?? 100 }} XP</p>
              </div>
              <div class="hidden md:block">
                <button class="px-3 py-1 rounded bg-[#1f2b2f] text-sm border border-gray-700">Ver logros</button>
              </div>
            </div>

            <div class="mt-3">
              <XPBar :percent="xpPercent" />
            </div>
          </div>

          <div class="mt-4 md:mt-0 md:w-64">
            <WalletSummary :user="user" />
          </div>
        </div>
      </div>
    </div>

    <!-- main content: left widgets + right feed -->
    <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6 pb-12">

      <!-- left column -->
      <div class="space-y-6 col-span-1">
        <AchievementsList :user="user" />
        <div class="bg-[#07121a] border border-gray-800 rounded-xl p-4">
          <h3 class="text-sm font-semibold text-neon-green mb-3">Acceso rápido</h3>
          <ul class="text-sm text-gray-300 space-y-2">
            <li>Insignias <span class="text-neon-green float-right"> {{ (user.badges ?? 0) }}</span></li>
            <li>Juegos <span class="text-neon-green float-right"> {{ (user.games ?? 0) }}</span></li>
            <li>Amigos <span class="text-neon-green float-right"> {{ (user.friends_count ?? 0) }}</span></li>
          </ul>
        </div>

        <FriendsList :friends="page.props.auth.friends || []" />
      </div>

      <!-- right column (wider) -->
      <div class="col-span-2 space-y-6">
        <RecentActivity :activities="page.props.auth.recent_activity || []" />
        <div class="bg-[#07121a] border border-gray-800 rounded-xl p-6">
          <h3 class="text-lg font-semibold text-neon-green mb-4">Juego favorito</h3>
          <div class="md:flex md:items-center md:gap-6">
            <div class="w-full md:w-48 h-32 bg-gray-800 rounded"></div>
            <div class="mt-3 md:mt-0">
              <h4 class="font-bold">Lucky Clover</h4>
              <p class="text-sm text-gray-300 mt-1">27 horas jugadas · 6 logros</p>
              <div class="mt-3">
                <button class="px-3 py-1 rounded bg-neon-green text-black font-semibold">Ver juego</button>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-[#07121a] border border-gray-800 rounded-xl p-6">
          <h3 class="text-lg font-semibold text-neon-green mb-4">Comentarios</h3>
          <textarea placeholder="Añadir un comentario..." class="w-full bg-[#0b1216] border border-gray-800 p-3 rounded text-sm text-gray-100 resize-none h-32"></textarea>
          <div class="mt-3 flex justify-end">
            <button class="px-4 py-2 rounded bg-neon-green text-black font-semibold">Publicar</button>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
/* neon color utility */
:root {
  --neon: #00ff66;
}
.text-neon-green { color: var(--neon); }
.bg-neon-green { background: var(--neon); }
.avatar-neon {
  box-shadow: 0 0 8px rgba(0,255,102,0.12), 0 0 20px rgba(0,255,102,0.08);
  transition: box-shadow .3s ease, transform .15s ease;
}
.avatar-neon:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 6px 30px rgba(0,255,102,0.18); }

/* thin border shadows for panels */
</style>
