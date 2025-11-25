<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { Link } from "@inertiajs/vue3";

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
    { name: 'Casino', icon: '🎰', active: true },
    { name: 'Slots', icon: '🎮' },
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
<style src="../../css/welcome.css"></style>
<template>
    <div class="app-container">

        <!-- Overlay para mobile -->
        <div 
            v-if="isMobile && isMobileMenuOpen" 
            class="mobile-overlay"
            @click="closeMobileMenu"
        ></div>

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
                <a href="#" class="nav-link active" @click="closeMobileMenu">
                    <i class="fas fa-home"></i>
                    <span class="nav-text">Home</span>
                </a>

                <a href="#" class="nav-link " @click="closeMobileMenu">
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

                <div class="header-right">
                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="btn-header btn-login"
                    >
                        Iniciar sesión
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="btn-header btn-register"
                    >
                        Registro
                    </Link>
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
    <!-- MODAL DE ADVERTENCIA -->
<div v-if="showWarning" class="modal-overlay" @click="closeWarning">
    <div class="modal-box" @click.stop>
        <button class="close-btn" @click="closeWarning">×</button>

        <img src="/logo.png" class="modal-logo" />

        <h2 class="modal-title">¡Bienvenido/a de nuevo!</h2>

        <input type="email" placeholder="Correo *" class="modal-input" />
        <input type="password" placeholder="Contraseña *" class="modal-input" />

        <Link :href="route('login')" class="modal-btn">
        INGRESAR
        </Link>

        <div class="modal-footer">
            ¿No tienes una cuenta? 
            <Link :href="route('register')" class="text-green-500 font-bold">
            Crear cuenta
            </Link>
        </div>
    </div>
</div>

</template>

