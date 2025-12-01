<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

/* -------------------------
   CATEGORÍAS
-------------------------- */
const categories = [
    { name: 'Casino', icon: '🎰',active: true },
    { name: 'Slots', icon: '🎮'},
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
    { id: 1, name: 'Crocodile Teeth', image: '/images/slots/Croco.png'},
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


</script>

<template>
    <AppLayout title="Dashboard">

        <!-- AQUÍ SOLO VA EL CONTENIDO REAL DEL DASHBOARD -->

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

        <!-- Sección: Exclusivos -->
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
            <!-- PIE DE PAGINA-->
            <footer class="footer-container">
                <div class="footer-grid">

                    <!-- Acerca de -->
                    <div class="footer-col">
                        <h3 class="footer-title">Acerca de</h3>
                            <ul>
                                <li><a class="footer-link" href="#">Nosotros</a></li>
                                <li><a class="footer-link" href="#">Promociones</a></li>
                                <li><a class="footer-link" href="#">Términos y Condiciones</a></li>
                                <li><a class="footer-link" href="#">Privacidad</a></li>
                                <li><a class="footer-link" href="#">Juego Responsable</a></li>
                             </ul>
                    </div>

                    <!-- Casino -->
                    <div class="footer-col">
                        <h3 class="footer-title">Casino</h3>
                            <ul>
                                <li><a class="footer-link" href="#">Los más jugados</a></li>
                                <li><a class="footer-link" href="#">Jackpots</a></li>
                                <li><a class="footer-link" href="#">Megaways</a></li>
                                <li><a class="footer-link" href="#">Alta volatilidad</a></li>
                            </ul>
                    </div>


                    <!-- Juegos -->
                    <div class="footer-col">
                        <h3 class="footer-title">Otros Juegos</h3>
                            <ul>
                                 <li><a class="footer-link" href="#">E-Sports</a></li>
                                <li><a class="footer-link" href="#">Crash Games</a></li>
                                <li><a class="footer-link" href="#">Virtuales</a></li>
                            </ul>
                    </div>

                    <!-- Ayuda -->
                    <div class="footer-col">
                        <h3 class="footer-title">Ayuda</h3>
                            <ul>
                                <li><a class="footer-link" href="#">Contáctenos</a></li>
                                <li><a class="footer-link" href="#">Recargas</a></li>
                                <li><a class="footer-link" href="#">Retiros</a></li>
                                <li><a class="footer-link" href="#">Preguntas frecuentes</a></li>
                                <li><a class="footer-link" href="#">Reclamos y disputas</a></li>
                            </ul>
                    </div>

               </div>

               <!-- Línea -->
               <div class="footer-divider"></div>

               <!-- Redes + App -->
                    <div class="footer-bottom">
                        <div class="footer-socials">
                             <a href="#" class="footer-social"><i class="fab fa-facebook-f"></i></a>
                             <a href="#" class="footer-social"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="footer-social"><i class="fab fa-tiktok"></i></a>
                            <a href="#" class="footer-social"><i class="fab fa-youtube"></i></a>
                        </div>

                    <a href="#" class="footer-app-btn">📲 Descargar App Android</a>

                    <a href="#" class="footer-book">📘 Libro de Reclamaciones</a>

               </div>

               <!-- Info -->
                <div class="footer-info">
                     <p>© 2025 Jackpots Celestial — Todos los derechos reservados.</p>
                    <p class="footer-warning">Los juegos de apuestas pueden causar ludopatía. Juega con responsabilidad.</p>
                </div>
            </footer>
    </AppLayout>
</template>
