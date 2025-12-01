<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/authStore';

// Store
const authStore = useAuthStore();
const page = usePage();

// Inicializa balance desde Inertia props
authStore.setBalance(page.props.auth.user.balance || 0);

// ✅ NUEVO: Observa cambios en los props de Inertia y actualiza el store
watch(() => page.props.auth.user.balance, (newBalance) => {
    if (newBalance !== undefined && newBalance !== null) {
        authStore.setBalance(newBalance);
    }
}, { immediate: true });

// ✅ Computed sin .value
const currentBalance = computed(() => {
    const bal = authStore.balance;
    return (typeof bal === 'number' ? bal : 0).toFixed(2);
});

// Datos del usuario
const user = page.props.auth.user;

// Menu y sidebar reactivo
const menuOpen = ref(false);
const isCollapsed = ref(false);
const isMobileMenuOpen = ref(false);
const isMobile = ref(false);

// Props
const props = defineProps({ title: String });

// Funciones de layout
const isActive = (path) => page.url.startsWith(path);

const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 768;
    if (isMobile.value) {
        isCollapsed.value = false;
        isMobileMenuOpen.value = false;
    }
};

const toggleSidebar = () => {
    if (isMobile.value) {
        isMobileMenuOpen.value = !isMobileMenuOpen.value;
    } else {
        isCollapsed.value = !isCollapsed.value;
    }
};

const closeMobileMenu = () => {
    if (isMobile.value) isMobileMenuOpen.value = false;
};

// Logout
const logout = () => router.post('/logout');

// Lifecycle
onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
});

onUnmounted(() => window.removeEventListener('resize', checkScreenSize));

</script>

<style src="../../css/oficial.css"></style>

<template>
    <div class="app-container">

        <!-- ========== SIDEBAR ========== -->
        <nav class="sidebar"
            :class="{ 
                collapsed: isCollapsed && !isMobile, 
                'mobile-open': isMobileMenuOpen && isMobile,
                'mobile-closed': !isMobileMenuOpen && isMobile
            }">

            <div class="sidebar-header">
                <h4 class="logo-text"></h4>

                <button class="toggle-btn-desktop" @click="toggleSidebar">
                    <i class="fas" :class="isCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
                </button>
            </div>

            <div class="nav-menu">
                <Link href="/dashboard" class="nav-link" :class="{ active: isActive('/dashboard') }">
                    <i class="fas fa-home"></i>
                    <span class="nav-text">Home</span>
                </Link>

                <Link href="/slots" class="nav-link":class="{ active: isActive('/slots') }">
                    <i class="fa-solid fa-dice"></i>
                    <span class="nav-text">Slots</span>
                </Link>

                <Link href="/crash" class="nav-link":class="{ active: isActive('/crash') }">
                    <i class="fa-solid fa-rocket"></i>
                    <span class="nav-text">Crash</span>
                </Link>

                <Link href="/juegosturbo" class="nav-link":class="{ active: isActive('/juegosturbo') }">
                    <i class="fa-solid fa-sack-dollar"></i>
                    <span class="nav-text">Juegos Turbo</span>
                </Link>

                <Link href="/roulette" class="nav-link":class="{ active: isActive('/roulette') }">
                    <i class="fa-solid fa-bullseye"></i>
                    <span class="nav-text">Ruleta</span>
                </Link>
            </div>
        </nav>


        <!-- ========== MAIN CONTENT ========== -->
        <main class="main-content">

            <!-- ========== HEADER ========== -->
            <header class="top-header">

                <div class="header-left">
                    <button class="hamburger-btn" @click="toggleSidebar">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="stake-logo">
                        <img src="/images/logo-celestiall.png" alt="Jackpots Celestial" class="logo-stake-img">
                    </div>
                </div>

                <div class="header-center">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" placeholder="Buscar..." class="search-input" />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <!-- ✅ SALDO REACTIVO - Se actualiza en tiempo real -->
                    <div class="bg-white/10 px-4 py-2 border rounded-xl min-w-[100px] text-center">
                        <span class="text-[10px] text-gray-300 uppercase font-bold tracking-wider">Saldo</span>
                        <span class="text-sm font-mono text-lime-400 font-bold">
                            S/ {{ currentBalance }}
                        </span>
                    </div>
                </div>

                    <div class="relative">
                        <button @click="menuOpen = !menuOpen" class="w-10 h-10 rounded-full overflow-hidden shadow">
                            <img :src="`/avatars/${user.avatar || 'avatar_default.png'}`" class="w-full h-full object-cover">
                        </button>

                        <!-- MENÚ DESPLEGABLE -->
                        <transition name="fade">
                            <div
                                v-if="menuOpen"
                                class="absolute right-0 mt-3 bg-[#0E021A] shadow-2xl rounded-xl w-64 p-3 z-50 border"
                            >

                                <!-- ITEM -->
                                <Link href="/profile" class="menu-item">
                                    <i class="fas fa-user text-gray-600"></i>
                                    <span>Mi perfil</span>
                                </Link>

                                <Link href="/wallet" class="menu-item">
                                    <i class="fas fa-plus-circle text-gray-600"></i>
                                    <span>Billetera</span>
                                </Link>




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
                
            </header>


            <!-- ========== AQUÍ IRÁ EL CONTENIDO DE LAS PÁGINAS ========== -->
            <section class="content-area">
                <slot />
            </section>


            <!-- ========== FOOTER ========== -->
            <footer class="footer-container">
                © 2025 Jackpots Celestial — Todos los derechos reservados.
            </footer>

        </main>
    </div>
</template>