<script setup>


import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;


const isOpen = ref(false);        // Sidebar móvil
const showMenu = ref(false);      // Menú del perfil
const saldo = ref(250.00);        // Luego viene de BD
const menuOpen = ref(false);

const logout = () => {
    router.post('/logout');
};

/* -------------------------
   SIDEBAR - REACTIVO
-------------------------- */
const isCollapsed = ref(false);
const isMobileMenuOpen = ref(false);
const isMobile = ref(false);

const toggleSidebar = () => {
    if (isMobile.value) {
        isMobileMenuOpen.value = !isMobileMenuOpen.value;
    } else {
        isCollapsed.value = !isCollapsed.value;
    }
};

const closeMobileMenu = () => {
    if (isMobile.value) {
        isMobileMenuOpen.value = false;
    }
};

const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 768;
    if (isMobile.value) {
        isCollapsed.value = false;
        isMobileMenuOpen.value = false;
    }
};

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
});

/* -------------------------
   PROPS INERTIA
-------------------------- */
defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

/* -------------------------
   CATEGORÍAS
-------------------------- */
const categories = [
    { name: 'Casino', icon: '🎰', },
    { name: 'Slots', icon: '🎮', active: true},
    { name: 'Exclusivos Jackpot Celestial', icon: '⭐' },
    { name: 'Función de compra', icon: '🛒' },
    { name: 'Estrenos Celestial', icon: '✨' },
];

/* -------------------------
   JUEGOS POR SECCIÓN
-------------------------- */
const featuredGames = [
    { 
        id: 1, 
        name: 'Angeles & Ladders - Live Casino', 
        subtitle: 'Prueba nuestro juego Snakes & Ladders en vivo',
        image: '/images/slots/lines.png',
        label: 'Promoción',
        btnText: '¡Juega ya!'
    },
    { 
        id: 2, 
        name: '$720,000 con Los 100 del Zeem', 
        subtitle: 'Juega los hits del Zeem, gana premios y desafíos',
        image: '/images/slots/zeem.jpg',
        label: 'Promoción',
        btnText: '¡Registrate Ya!'
    },
    { 
        id: 3, 
        name: 'Juego exclusivo: Mines', 
        subtitle: 'Descubre, juega y conquista tu premio en Mines',
        image: '/images/slots/mines-feature.jpg',
        label: 'Exclusivo',
        btnText: '¡Juega ya!'
    },
];

const slotsGames = [
    { id: 1, name: 'Gates Olympus', image: '/images/slots/olimpus.png' },
    { id: 2, name: 'Sugar Rush', image: '/images/slots/sugar.png' },
    { id: 3, name: '20 hot blast', image: '/images/slots/hot.png' },
    { id: 4, name: 'Shining Crown', image: '/images/slots/shining.png' },
    { id: 5, name: 'More Lucky & Wild', image: '/images/slots/lucky.png' },
    { id: 6, name: 'Gates Olympus', image: '/images/slots/olimpus.png' },
    { id: 7, name: 'Sugar Rush', image: '/images/slots/sugar.png' },
    { id: 8, name: '20 hot blast', image: '/images/slots/hot.png' },
];

const turboGames = [
    { id: 1, name: 'Mines', image: '/images/slots/minas.png' },
    { id: 2, name: 'Plinko', image: '/images/slots/plinko.png' },
    { id: 3, name: 'Wheel', image: '/images/slots/wheel.png' },
    { id: 4, name: 'High Flyer', image: '/images/slots/high.png' },
    { id: 5, name: 'Lines', image: '/images/slots/lines.png' },
    { id: 6, name: 'Mines', image: '/images/slots/minas.png' },
    { id: 7, name: 'Plinko', image: '/images/slots/plinko.png' },
    { id: 8, name: 'High Flyer', image: '/images/slots/high.png' },
];

const exclusiveGames = [
    { id: 1, name: 'Lines', image: '/images/slots/lines.png' },
    { id: 2, name: 'High Flyer', image: '/images/slots/high.png' },
    { id: 3, name: 'Mines', image: '/images/slots/minas.png' },
    { id: 4, name: '20 hot blast', image: '/images/slots/hot.png' },
    { id: 5, name: 'Plinko', image: '/images/slots/plinko.png' },
    { id: 6, name: 'Wheel', image: '/images/slots/wheel.png' },
    { id: 7, name: 'Gates Olympus', image: '/images/slots/olimpus.png' },
    { id: 8, name: 'Shining Crown', image: '/images/slots/shining.png' },
];
const showWarning = ref(false);

const openWarning = () => {
    showWarning.value = true;
};

const closeWarning = () => {
    showWarning.value = false;
};

</script>
<style src="../../css/oficial.css"></style>
<template>
    <Head title="Dashboard" />

    <div class="flex h-screen bg-gray-900 text-white overflow-hidden">

        <!-- ===== SIDEBAR ===== -->
        <nav class="sidebar"
             :class="{ 
                 collapsed: isCollapsed && !isMobile, 
                 'mobile-open': isMobileMenuOpen && isMobile,
                 'mobile-closed': !isMobileMenuOpen && isMobile
             }">

            <!-- Logo y toggle -->
            <div class="sidebar-header">
                <h4 class="logo-text">Logo</h4>
                <button class="toggle-btn-desktop" @click="toggleSidebar">
                    <i class="fas" :class="isCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
                </button>
            </div>

            <!-- Navigation -->
            <div class="nav-menu">
                <a href="#" class="nav-link " @click="closeMobileMenu">
                    <i class="fas fa-home"></i>
                    <span class="nav-text">Home</span>
                </a>

                <a href="#" class="nav-link active" @click="closeMobileMenu">
                    <i class="fa-solid fa-dice"></i>
                    <span class="nav-text">Slots</span>
                </a>

                <a href="#" class="nav-link" @click="closeMobileMenu">
                    <i class="fa-solid fa-rocket"></i>
                    <span class="nav-text">Crash</span>
                </a>

                <a href="#" class="nav-link" @click="closeMobileMenu">
                    <i class="fa-solid fa-sack-dollar"></i>
                    <span class="nav-text">Jackpots</span>
                </a>

                <a href="#" class="nav-link" @click="closeMobileMenu">
                    <i class="fa-solid fa-bullseye"></i>
                    <span class="nav-text">Ruulete</span>
                </a>

                <a href="#" class="nav-link" @click="closeMobileMenu">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Comunidad</span>
                </a>

                <a href="#" class="nav-link" @click="closeMobileMenu">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Settings</span>
                </a>
            </div>
            
        </nav>

        <!-- ===== MAIN CONTENT ===== -->
        <main class="main-content">

            <!-- HEADER -->
            <header class="top-header">
                
                <div class="header-left">
                    <!-- Botón hamburguesa para mobile -->
                    <button class="hamburger-btn" @click="toggleSidebar">
                        <i class="fas fa-bars"></i>
                    </button>

                    <!-- Logo Stake -->
                    <div class="stake-logo">
                        <span class="logo-stake">Jackpots Celestial</span>
                    </div>

                    <!-- Botón volver (mobile) -->
                    <button class="back-btn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>

                <div class="header-center">
                    <!-- Buscador -->
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" placeholder="Buscar..." class="search-input" />
                    </div>
                </div>

                <div class="flex items-center gap-4">

                    <!-- CUADRO DE SALDO -->
                       <div class="bg-white border border-gray-300 px-4 py-2 rounded-xl shadow flex flex-col text-center">

                    </div>

                    <!-- BOTÓN PERFIL -->
                    <div class="relative">
                       <button 
                            @click="menuOpen = !menuOpen"
                            class="w-10 h-10 rounded-full overflow-hidden shadow"
                        >
                            <img 
                            :src="`/avatars/${user.avatar || 'avatar_default.png'}`"
                            alt="avatar"
                            class="w-full h-full object-cover"
                            />
                        </button>

                        <!-- MENÚ DESPLEGABLE -->
                        <transition name="fade">
                            <div
                                v-if="menuOpen"
                                class="absolute right-0 mt-3 bg-white shadow-2xl rounded-xl w-64 p-3 z-50 border"
                            >

                                <!-- ITEM -->
                                <Link href="/profile" class="menu-item">
                                    <i class="fas fa-user text-gray-600"></i>
                                    <span>Mi perfil</span>
                                </Link>

                                <div class="menu-item" >
                                    <i class="fas fa-plus-circle text-gray-600"></i>
                                    <span>Recargar</span>
                                </div>

                                <div class="menu-item">
                                    <i class="fas fa-wallet text-gray-600"></i>
                                    <span>Retirar</span>
                                </div>

                                <div class="menu-item">
                                    <i class="fas fa-clock text-gray-600"></i>
                                    <span>Historial de saldo</span>
                                </div>

                                <div class="menu-item">
                                    <i class="fas fa-trophy text-gray-600"></i>
                                    <span>Mis apuestas deportivas</span>
                                </div>

                             

                                <div class="menu-item">
                                    <i class="fas fa-envelope text-gray-600"></i>
                                    <span>Mis notificaciones</span>
                                </div>

                                <div class="menu-item">
                                    <i class="fas fa-eye-slash text-gray-600"></i>
                                    <span>Ocultar saldo</span>
                               </div>

                                <div class="menu-item">
                                    <i class="fas fa-volume-up text-gray-600"></i>
                                    <span>Sonidos</span>
                                    <div class="ml-auto">
                                        <input type="checkbox" class="toggle" />
                                    </div>
                                </div>

                                <!-- CERRAR SESIÓN -->
                                <button
                                    @click="logout"
                                    class="w-full mt-2 text-red-600 font-bold flex items-center gap-2 p-2"
                                >
                                    <i class="fas fa-power-off"></i>
                                    Cerrar sesión
                                </button>
                            </div>
                        </transition>
                    </div>
                </div>
            </header>
           

            <!-- CONTENIDO -->
            <div class="content-area">

                <!-- Featured Cards -->
                <div class="featured-section">
                    <div 
                        v-for="card in featuredGames" 
                        :key="card.id"
                        class="featured-card"
                        :style="{ backgroundImage: `url(${card.image})` }"
                    >
                        <div class="featured-overlay">
                            <span class="featured-label">{{ card.label }}</span>
                            <h3 class="featured-title">{{ card.name }}</h3>
                            <p class="featured-subtitle">{{ card.subtitle }}</p>
                            <button class="featured-btn">{{ card.btnText }}</button>
                        </div>
                    </div>
                </div>

                <!-- Categorías horizontales -->
                <div class="categories-wrapper">
                    <div class="categories-scroll">
                        <button 
                            v-for="cat in categories" 
                            :key="cat.name"
                            class="category-chip"
                            :class="{ active: cat.active }"
                        >
                            <span>{{ cat.icon }}</span>
                            <span>{{ cat.name }}</span>
                        </button>
                    </div>
                </div>

                <!-- Sección: Slots -->
                <section class="game-section">
                    <div class="section-header-row">
                        <h2 class="section-title">
                            <i class="fas fa-fire section-icon"></i>
                            Slots
                        </h2>
                        <button class="see-all-btn">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    <div class="games-horizontal">
                        <a 
                            v-for="game in slotsGames"
                            :key="game.id"
                            href="#"
                            class="game-item"
                            @click.prevent="openWarning"
                        >
                            <div class="game-image-wrapper">
                                <img :src="game.image" :alt="game.name" class="game-img" />
                                <div class="game-hover-overlay">
                                    <button class="game-play-icon">▶</button>
                                </div>
                            </div>
                            <p class="game-title">{{ game.name }}</p>
                        </a>
                    </div>
                </section>

                <!-- Sección: Juegos Turbo -->
                <section class="game-section">
                    <div class="section-header-row">
                        <h2 class="section-title">
                            <i class="fas fa-bolt section-icon"></i>
                            Juegos Turbo
                        </h2>
                        <button class="see-all-btn">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    <div class="games-horizontal">
                        <a 
                            v-for="game in turboGames"
                            :key="game.id"
                            href="#"
                            class="game-item"
                            @click.prevent="openWarning"
                        >
                            <div class="game-image-wrapper">
                                <img :src="game.image" :alt="game.name" class="game-img" />
                                <div class="game-hover-overlay">
                                    <button class="game-play-icon">▶</button>
                                </div>
                            </div>
                            <p class="game-title">{{ game.name }}</p>
                        </a>
                    </div>
                </section>

                <!-- Sección: Exclusivos Stake -->
                <section class="game-section">
                    <div class="section-header-row">
                        <h2 class="section-title">
                            <i class="fas fa-star section-icon"></i>
                            Exclusivos Stake
                        </h2>
                        <button class="see-all-btn">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    <div class="games-horizontal">
                        <a 
                            v-for="game in exclusiveGames"
                            :key="game.id"
                            href="#"
                            class="game-item"
                            @click.prevent="openWarning"
                        >
                            <div class="game-image-wrapper">
                                <span class="game-provider-badge">{{ game.provider }}</span>
                                <img :src="game.image" :alt="game.name" class="game-img" />
                                <div class="game-hover-overlay">
                                    <button class="game-play-icon">▶</button>
                                </div>
                            </div>
                            <p class="game-title">{{ game.name }}</p>
                        </a>
                    </div>
                </section>

            </div>
        </main>
       

       

       
    </div>
</template>


