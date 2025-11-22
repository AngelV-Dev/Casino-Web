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

<style scoped>
@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css");

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.app-container {
    display: flex;
    background: #182426;
    min-height: 100vh;
    color: #fff;
}

/* ===== SIDEBAR ===== */
.sidebar {
    width: 240px;
    background: #182426;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    border-right: 1px solid rgba(255, 255, 255, 0.05);
}

.sidebar.collapsed {
    width: 70px;
}

.sidebar-header {
    padding: 1.5rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.logo-text {
    font-size: 1.25rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
    white-space: nowrap;
    transition: opacity 0.2s;
}

.sidebar.collapsed .logo-text {
    opacity: 0;
}

.toggle-btn-desktop {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.6);
    font-size: 1rem;
    cursor: pointer;
    padding: 0.5rem;
    transition: color 0.2s;
}

.toggle-btn-desktop:hover {
    color: #fff;
}

.nav-menu {
    flex: 1;
    padding: 1rem 0;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.875rem 1.25rem;
    color: rgba(255, 255, 255, 0.6);
    text-decoration: none;
    transition: all 0.2s;
    gap: 1rem;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.05);
    color: #fff;
}

.nav-link.active {
    background: rgba(0, 255, 163, 0.1);
    color: #00ffa3;
    border-left: 3px solid #00ffa3;
}

.nav-link i {
    font-size: 1.25rem;
    width: 24px;
    text-align: center;
}

.nav-text {
    font-size: 0.9rem;
    font-weight: 500;
    white-space: nowrap;
    transition: opacity 0.2s;
}

.sidebar.collapsed .nav-text {
    opacity: 0;
}

.sidebar-profile {
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.profile-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
}

.profile-info {
    flex: 1;
    transition: opacity 0.2s;
}

.sidebar.collapsed .profile-info {
    opacity: 0;
}

.profile-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: #fff;
}

.profile-role {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.5);
}

/* ===== MAIN CONTENT ===== */
.main-content {
    margin-left: 240px;
    flex: 1;
    transition: margin-left 0.3s ease;
    width: calc(100% - 240px);
}

.sidebar.collapsed + .main-content {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* ===== HEADER ===== */
.top-header {
    background: #182426;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.hamburger-btn {
    display: none;
    background: transparent;
    border: none;
    color: #fff;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
}

.back-btn {
    display: none;
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.6);
    font-size: 1.2rem;
    cursor: pointer;
}

.stake-logo {
    font-size: 1.5rem;
    font-weight: 700;
}

.logo-stake {
    background: linear-gradient(135deg, #42cc5eff 0%, #00ff80ff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.header-center {
    flex: 1;
    max-width: 500px;
}

.search-box {
    position: relative;
    width: 100%;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.4);
    font-size: 0.9rem;
}

.search-input {
    width: 100%;
    background: #0f212e;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    color: #fff;
    font-size: 0.9rem;
}

.search-input:focus {
    outline: none;
    border-color: #00ff55ff;
}

.search-input::placeholder {
    color: rgba(255, 255, 255, 0.4);
}

.header-right {
    display: flex;
    gap: 0.75rem;
}

.btn-header {
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-login {
    background: transparent;
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-login:hover {
    background: rgba(255, 255, 255, 0.05);
}

.btn-register {
    background: linear-gradient(135deg, #00ffa3 0%, #00ff59ff 100%);
    color: #0f212e;
    border: none;
    font-weight: 700;
}

.btn-register:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 85, 27, 0.3);
}

/* ===== CONTENT AREA ===== */
.content-area {
    padding: 1.5rem;
}

/* Featured Cards */
.featured-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.featured-card {
    background-size: cover;
    background-position: center;
    border-radius: 12px;
    min-height: 180px;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.3s;
}

.featured-card:hover {
    transform: translateY(-4px);
}

.featured-overlay {
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%);
    padding: 1.5rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.featured-label {
    background: #00ff6ee5;
    color: #0f212e;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    align-self: flex-start;
}

.featured-title {
    font-size: 1.125rem;
    font-weight: 700;
    margin: 0.5rem 0 0.25rem;
    color: #fff;
}

.featured-subtitle {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 1rem;
}

.featured-btn {
    background: #00ff84ff;
    color: #0f212e;
    border: none;
    padding: 0.625rem 1.25rem;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.875rem;
    cursor: pointer;
    align-self: flex-start;
    transition: all 0.2s;
}

.featured-btn:hover {
    background: #00d486;
    transform: scale(1.05);
}

/* Categories */
.categories-wrapper {
    margin-bottom: 2rem;
    overflow: hidden;
}

.categories-scroll {
    display: flex;
    gap: 0.75rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
}

.categories-scroll::-webkit-scrollbar {
    height: 6px;
}

.categories-scroll::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.category-chip {
    background: #1a3827ff;
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.7);
    padding: 0.625rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.category-chip:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.2);
}

.category-chip.active {
    background: rgba(0, 255, 163, 0.15);
    border-color: #00ffa3;
    color: #00ffa3;
}

/* Game Sections */
.game-section {
    margin-bottom: 2.5rem;
}

.section-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-icon {
    color: #00ffa3;
}

.see-all-btn {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.5);
    font-size: 1rem;
    cursor: pointer;
    padding: 0.5rem;
    transition: color 0.2s;
}

.see-all-btn:hover {
    color: #00ffa3;
}

/* Games Horizontal Scroll */
.games-horizontal {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
}

.games-horizontal::-webkit-scrollbar {
    height: 6px;
}

.games-horizontal::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.game-item {
    flex: 0 0 160px;
    text-decoration: none;
    cursor: pointer;
}

.game-image-wrapper {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    aspect-ratio: 3/4;
    background: #1a2c38;
}

.game-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.game-item:hover .game-img {
    transform: scale(1.1);
}

.game-provider-badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    z-index: 2;
}

.game-hover-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.game-item:hover .game-hover-overlay {
    opacity: 1;
}

.game-play-icon {
    background: #00ffa3;
    color: #0f212e;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s;
}

.game-play-icon:hover {
    transform: scale(1.1);
}

.game-title {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
    font-weight: 500;
    margin-top: 0.5rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Mobile Overlay */
.mobile-overlay {
    display: none;
}

/* Mobile Overlay */
.mobile-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    /* Sidebar mobile */
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.mobile-open {
        transform: translateX(0);
    }
    
    .mobile-overlay {
        display: block;
    }
    
    .main-content {
        margin-left: 0;
        width: 100%;
    }
    
    .sidebar.collapsed + .main-content {
        margin-left: 0;
        width: 100%;
    }
    
    /* Header mobile */
    .hamburger-btn {
        display: block;
    }
    
    .back-btn {
        display: block;
    }
    
    .stake-logo {
        font-size: 1.25rem;
    }
    
    .top-header {
        padding: 0.75rem 1rem;
    }
    
    .header-center {
        max-width: none;
    }
    
    .search-box {
        display: none;
    }
    
    .btn-header {
        padding: 0.5rem 0.875rem;
        font-size: 0.8rem;
    }
    
    /* Content */
    .content-area {
        padding: 1rem;
    }
    
    /* Featured cards mobile */
    .featured-section {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .featured-card {
        min-height: 160px;
    }
    
    .featured-overlay {
        padding: 1rem;
    }
    
    .featured-title {
        font-size: 1rem;
    }
    
    .featured-subtitle {
        font-size: 0.8rem;
    }
    
    /* Categories */
    .categories-scroll {
        gap: 0.5rem;
    }
    
    .category-chip {
        padding: 0.5rem 0.875rem;
        font-size: 0.8rem;
    }
    
    /* Games */
    .game-item {
        flex: 0 0 140px;
    }
    
    .section-title {
        font-size: 1.125rem;
    }
    
    .game-section {
        margin-bottom: 2rem;
    }
}

@media (max-width: 480px) {
    .header-right {
        gap: 0.5rem;
    }
    
    .btn-header {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
    }
    
    .featured-card {
        min-height: 140px;
    }
    
    .game-item {
        flex: 0 0 120px;
    }
    
    .game-play-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}

@media (min-width: 1400px) {
    .game-item {
        flex: 0 0 180px;
    }
}

/* Fondo oscuro */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, .7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

/* Caja del modal */
.modal-box {
    background: #141a16ff;
    width: 380px;
    padding: 30px;
    border-radius: 12px;
    position: relative;
    color: white;
    box-shadow: 0 0 20px rgba(0,0,0,0.4);
}

/* Botón cerrar */
.close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 22px;
    background: transparent;
    border: none;
    color: #fff;
    cursor: pointer;
}

/* Imagen de logo */
.modal-logo {
    display: block;
    width: 160px;
    margin: 0 auto 10px;
}

/* Título */
.modal-title {
    text-align: center;
    font-size: 20px;
    margin-bottom: 20px;
}

/* Inputs */
.modal-input {
    width: 100%;
    background: #222;
    border: 1px solid #333;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    color: white;
}

/* Botón ingresar */
.modal-btn {
    width: 100%;
    background: #09b84fff;
    padding: 12px;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 10px;
    
}

/* Footer */
.modal-footer {
    text-align: center;
    margin-top: 15px;
    font-size: 14px;
}

.modal-footer a {
    color: #0fa448;
    font-weight: bold;
    
}

</style>