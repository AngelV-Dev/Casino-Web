<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const juegosCrash = [
    {
        id: 1,
        name: 'High Flyer',
        image: '/images/slots/high.png',
        routeName: 'high-flyer'
    },
    {
        id: 2,
        name: 'Big Bass Crash',
        image: '/images/slots/high.png',
        routeName: 'high-flyer'
    },
    {
        id: 3,
        name: 'Spaceman',
        image: '/images/slots/high.png',
        routeName: 'high-flyer'
    }
];

</script>

<template>
    <AppLayout title="Crash">

        <!-- BANNER -->
        <div class="crash-banner">
            <img src="images/slots/high1.png" alt="Crash Banner">
        </div>

        <!-- JUEGOS -->
        <div class="crash-games">
            <div 
                v-for="game in juegosCrash" 
                :key="game.id"
                class="crash-card"
            >
                <a :href="route(game.routeName)">
                <img :src="game.image" :alt="game.name">
                </a>
            </div>
        </div>

        <!-- BOTÓN VER MÁS -->
        <div class="crash-btn-wrapper">
            <button class="crash-btn-more">Ver más</button>
        </div>
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

<style scoped>
.crash-banner img {
    width: 100%;
    border-radius: 14px;
    margin-bottom: 20px;
}

/* cards */
.crash-games {
    display: flex;
    gap: 20px;
    justify-content: center;
}

.crash-card {
    width: 300px;
    height: 180px;
    overflow: hidden;
    border-radius: 12px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.crash-card:hover {
    transform: translateY(-6px);
}

.crash-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* botón */
.crash-btn-wrapper {
    margin-top: 30px;
    text-align: center;
}



</style>
