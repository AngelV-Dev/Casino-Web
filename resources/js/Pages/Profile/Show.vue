<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';

// Obtiene las propiedades de Inertia
const page = usePage();
// Asumiendo que user tiene datos como level, xp, games, wins, streak, etc.
const user = page.props.auth.user || {}; 

// --- ESTADOS PRINCIPALES ---
const achievements = ref([]);
const activities = ref([]);
const favoriteGame = ref(null);
const loading = ref(true); // Estado de carga para mostrar el esqueleto

// Definiciones de logros con emojis y clases de color para Tailwind
const achievementDefinitions = {
    'account_created': { emoji: '🎉', color: 'from-blue-600 to-blue-800' },
    'first_deposit': { emoji: '💰', color: 'from-yellow-600 to-yellow-800' },
    'win_streak_5': { emoji: '🔥', color: 'from-red-600 to-red-800' },
    'earnings_100': { emoji: '💵', color: 'from-green-600 to-green-800' },
    'played_50_games': { emoji: '🎮', color: 'from-purple-600 to-purple-800' }
};

// --- PROPIEDADES CALCULADAS ---

// Cálculo del porcentaje de XP para la barra de progreso
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

// Amigos (asumiendo que se pasan como prop de Inertia)
const friends = page.props.friends || [];


// --- FUNCIÓN DE UTILIDAD ---

/**
 * Cuenta el número de logros que tienen la propiedad 'unlocked' como true.
 * @returns {number}
 */
const getUnlockedCount = () => {
    if (!Array.isArray(achievements.value)) {
        return 0; 
    }
    return achievements.value.filter(a => a && a.unlocked).length;
};


// --- CARGA DE DATOS (CORRECCIÓN CLAVE) ---
onMounted(async () => {
    try {
        loading.value = true;
        
        const [achievRes, activRes, gameRes] = await Promise.all([
            // Manejo de errores en la API para que Promise.all no falle por una sola petición
            axios.get('/api/user/achievements').catch((e) => { console.error("Error logros:", e); return { data: { achievements: {} } } }),
            axios.get('/api/user/activities').catch((e) => { console.error("Error actividades:", e); return { data: { activities: [] } } }),
            axios.get('/profile/favorite-game').catch((e) => { console.error("Error juego favorito:", e); return { data: { favoriteGame: null } } }),
        ]);

        // 1. CORRECCIÓN DE LOGROS: Asegura que achievements.value sea un ARRAY.
        // Si la API devuelve un objeto (ej. { key1: {...}, key2: {...} }), Object.values() lo convierte a un array de objetos.
        const rawAchievements = achievRes.data?.achievements || {};
        let achDataArray = Object.values(rawAchievements);

        // Además, mezcla las definiciones (emoji/color) con los datos del logro
        if (Array.isArray(achDataArray)) {
            achievements.value = achDataArray.map(ach => ({
                ...ach,
                // Agrega el color y emoji basado en la 'key' del logro
                ...achievementDefinitions[ach.key] 
            }));
        } else {
            achievements.value = [];
        }

        // 2. Actividades
        let actData = activRes.data?.activities || [];
        activities.value = Array.isArray(actData) ? actData : [];

        // 3. Juego Favorito
        favoriteGame.value = gameRes.data?.favoriteGame || null;

    } catch (error) {
        console.error('Error fatal al cargar datos:', error);
        achievements.value = [];
        activities.value = [];
        favoriteGame.value = null;
    } finally {
        loading.value = false;
    }
    
    document.body.classList.add('profile-loaded');
});

// --- FORMATTERS ---

/**
 * Formatea una fecha para visualización.
 * @param {string} date - La cadena de fecha.
 * @returns {string}
 */
const formatDate = (date) => {
    if (!date) return 'Hace poco';
    try {
        // Usa una opción más corta para el formato de tiempo en el perfil
        return new Date(date).toLocaleDateString('es-PE', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch {
        return 'Fecha no válida';
    }
};

/**
 * Genera una descripción legible para la actividad.
 * @param {object} activity - El objeto de actividad.
 * @returns {string}
 */
const getActivityDescription = (activity) => {
    if (activity.activity_type === 'game_played') {
        // Asumiendo que 'amount' y 'game_name' vienen en el objeto de actividad
        const amountDisplay = activity.amount ? `S/ ${Math.abs(activity.amount).toFixed(2)}` : '';
        return activity.won 
            ? `🎉 Ganaste ${amountDisplay} en ${activity.game_name}`
            : `😢 Perdiste en ${activity.game_name}`;
    }
    return activity.description || 'Actividad registrada';
};

/**
 * Define el color del borde para la actividad.
 * @param {object} activity - El objeto de actividad.
 * @returns {string}
 */
const getActivityColor = (activity) => {
    if (activity.activity_type === 'game_played') {
        return activity.won ? 'border-l-green-500 bg-green-500/5' : 'border-l-red-500 bg-red-500/5';
    }
    return 'border-l-lime-500 bg-lime-500/5';
};

</script>

<template>
    <Head title="Mi Perfil" />
    
    <AppLayout>
        <div class="min-h-screen bg-[#0a0b0d] pb-16">
            
            <div class="relative h-64 md:h-80 overflow-hidden">
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
                        <div class="flex flex-col md:flex-row items-center md:items-end gap-6">
                            
                            <div class="relative group flex-shrink-0">
                                <div class="absolute -inset-1 rounded-2xl blur opacity-75 group-hover:opacity-100 transition bg-lime-500"></div>
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
                                    <span class="px-4 py-1.5 rounded-full text-black text-sm font-bold uppercase tracking-wider shadow-lg inline-block bg-lime-500">
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
                                class="hidden md:flex items-center gap-2 px-6 py-3 bg-lime-500 hover:bg-lime-400 text-black font-bold rounded-xl shadow-lg transition transform hover:scale-105 flex-shrink-0"
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
                                    :src="`/avatars/${friend.avatar || 'avatar_default.png'}`" 
                                    class="w-12 h-12 rounded-lg object-cover"
                                />
                                <div class="flex-1">
                                    <div class="text-white font-semibold text-sm">{{ friend.name }}</div>
                                    <div class="text-xs text-gray-400">{{ friend.last_online || 'En línea' }}</div>
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

                <div v-if="!loading" class="lg:col-span-2 space-y-6">

                    <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl">
                        <h3 class="text-xl font-bold text-lime-500 mb-6 flex items-center gap-2">
                            <i class="fas fa-trophy"></i>
                            Logros Desbloqueados ({{ getUnlockedCount() }}/{{ achievements.length }})
                        </h3>
                        
                        <div v-if="achievements.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            <div 
                                v-for="achievement in achievements" 
                                :key="achievement.key"
                                class="group relative"
                            >
                                <div 
                                    :class="[
                                        'aspect-square rounded-2xl border-2 flex flex-col items-center justify-center p-2 text-center transition transform hover:scale-105 cursor-pointer',
                                        achievement.unlocked 
                                            ? `bg-gradient-to-br ${achievement.color || 'from-lime-600 to-lime-800'} border-lime-500 shadow-lg shadow-lime-500/30`
                                            : 'bg-gray-900/50 border-gray-700 hover:border-gray-600'
                                    ]"
                                >
                                    <div v-if="achievement.unlocked" class="text-center">
                                        <div class="text-4xl mb-2">{{ achievement.emoji }}</div>
                                        <p class="text-white font-bold text-xs md:text-sm leading-tight">{{ achievement.title }}</p>
                                        <p class="text-white/60 text-[10px] mt-1">{{ formatDate(achievement.unlocked_at) }}</p>
                                    </div>

                                    <div v-else class="text-center">
                                        <i class="fas fa-lock text-3xl text-gray-500 mb-2"></i>
                                        <p class="text-gray-400 text-xs font-semibold">Bloqueado</p>
                                        <p class="text-gray-500 text-[9px] mt-1">{{ achievement.description }}</p>
                                    </div>
                                </div>

                                <div class="absolute -top-12 left-1/2 -translate-x-1/2 bg-gray-900 border border-gray-700 rounded-lg p-2 text-xs text-white opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap z-10 shadow-xl">
                                    {{ achievement.description }}
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-8 text-gray-500">
                            <i class="fas fa-lock text-4xl mb-2 opacity-30"></i>
                            <p class="text-sm">Sin logros disponibles o cargados</p>
                        </div>
                    </div>

                    <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl">
                        <h3 class="text-xl font-bold text-lime-500 mb-6 flex items-center gap-2">
                            <i class="fas fa-history"></i>
                            Actividad Reciente
                        </h3>
                        
                        <div v-if="activities.length > 0" class="space-y-3">
                            <div 
                                v-for="(activity, idx) in activities.slice(0, 8)" 
                                :key="idx"
                                :class="['flex items-start gap-4 p-4 rounded-xl border-l-4 transition', getActivityColor(activity)]"
                            >
                                <div class="w-12 h-12 rounded-lg bg-gray-800/50 flex items-center justify-center flex-shrink-0 text-2xl">
                                    <span v-if="activity.activity_type === 'game_played' && activity.won">🎉</span>
                                    <span v-else-if="activity.activity_type === 'game_played'">🎮</span>
                                    <span v-else>📝</span>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <p class="text-white font-semibold text-sm">{{ getActivityDescription(activity) }}</p>
                                    <p class="text-gray-400 text-xs mt-1">{{ formatDate(activity.happened_at) }}</p>
                                </div>

                                <div v-if="activity.amount" class="text-right flex-shrink-0">
                                    <p :class="['font-bold text-sm', activity.won ? 'text-green-400' : 'text-red-400']">
                                        {{ activity.won ? '+' : '-' }}S/ {{ Math.abs(activity.amount).toFixed(2) }}
                                    </p>
                                </div>
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

                    <div v-if="favoriteGame" class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl">
                        <h3 class="text-xl font-bold text-lime-500 mb-6 flex items-center gap-2">
                            <i class="fas fa-star"></i>
                            Juego Favorito
                        </h3>
                        
                        <div class="md:flex items-center gap-6">
                            <div class="w-full md:w-64 h-48 bg-gradient-to-br from-purple-900/40 to-lime-900/40 rounded-2xl overflow-hidden relative group mb-4 md:mb-0">
                                <img :src="favoriteGame.image" :alt="favoriteGame.name" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                    <button class="px-4 py-2 bg-lime-500 hover:bg-lime-400 text-black font-bold rounded-lg transition">
                                        <i class="fas fa-play mr-2"></i>Jugar
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex-1">
                                <h4 class="text-2xl font-bold text-white mb-2">{{ favoriteGame.name }}</h4>
                                
                                <div class="grid grid-cols-3 gap-4 mb-4">
                                    <div class="bg-gray-800/50 rounded-lg p-3 text-center">
                                        <p class="text-gray-400 text-xs font-bold uppercase">Horas</p>
                                        <p class="text-2xl font-bold text-lime-400 mt-1">{{ favoriteGame.hoursPlayed || 0 }}</p>
                                    </div>
                                    <div class="bg-gray-800/50 rounded-lg p-3 text-center">
                                        <p class="text-gray-400 text-xs font-bold uppercase">Jugadas</p>
                                        <p class="text-2xl font-bold text-blue-400 mt-1">{{ favoriteGame.gamesPlayed || 0 }}</p>
                                    </div>
                                    <div class="bg-gray-800/50 rounded-lg p-3 text-center">
                                        <p class="text-gray-400 text-xs font-bold uppercase">Victorias</p>
                                        <p class="text-2xl font-bold text-green-400 mt-1">{{ favoriteGame.gamesWon || 0 }}</p>
                                    </div>
                                </div>

                                <div v-if="favoriteGame.gamesPlayed > 0" class="mb-4">
                                    <p class="text-gray-400 text-sm mb-2">Tasa de Victoria</p>
                                    <div class="w-full bg-gray-700 rounded-full h-3 overflow-hidden">
                                        <div 
                                            class="bg-gradient-to-r from-lime-500 to-green-600 h-full transition-all"
                                            :style="{ width: ((favoriteGame.gamesWon / favoriteGame.gamesPlayed) * 100) + '%' }"
                                        ></div>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">{{ ((favoriteGame.gamesWon / favoriteGame.gamesPlayed) * 100).toFixed(1) }}%</p>
                                </div>

                                <Link href="/profile/edit" class="inline-flex items-center gap-2 px-4 py-2 bg-lime-500 hover:bg-lime-400 text-black font-bold rounded-lg transition">
                                    <i class="fas fa-edit"></i>
                                    Cambiar Favorito
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-else class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-8 text-center shadow-2xl">
                        <i class="fas fa-gamepad text-5xl text-gray-600 mb-4"></i>
                        <p class="text-gray-400 mb-4">Aún no has seleccionado un juego favorito</p>
                        <Link href="/profile/edit" class="inline-flex items-center gap-2 px-6 py-3 bg-lime-500 hover:bg-lime-400 text-black font-bold rounded-lg transition">
                            <i class="fas fa-star"></i>
                            Elegir Ahora
                        </Link>
                    </div>

                </div>

                <div v-else class="lg:col-span-2 space-y-6">
                    <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl animate-shimmer h-48"></div>
                    <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl animate-shimmer h-64"></div>
                    <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl animate-shimmer h-56"></div>
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

/* Animación shimmer para el estado de carga */
@keyframes shimmer {
    0% { background-position: -1000px 0; }
    100% { background-position: 1000px 0; }
}

.animate-shimmer {
    animation: shimmer 2s infinite;
    /* Un degradado gris para el efecto de luz moviéndose */
    background: linear-gradient(to right, transparent 0%, rgba(255,255,255,0.1) 50%, transparent 100%);
    background-size: 1000px 100%;
    background-color: #1a1d24; /* Color base del skeleton */
}

.transition {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>