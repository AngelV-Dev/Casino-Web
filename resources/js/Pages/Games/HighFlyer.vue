<script setup>
import { ref, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useAuthStore } from '@/stores/authStore'; // ✅ IMPORTAR STORE
import axios from 'axios'; // ✅ USAR AXIOS en lugar de router.post

// ✅ Usar el store en lugar de balance local
const authStore = useAuthStore();

// ✅ Balance reactivo desde el store
const balance = computed(() => authStore.balance);

/* -----------------------------
   VARIABLES PRINCIPALES
----------------------------- */
const betAmount = ref(10);
const multiplier = ref(1.00);
const multiplierStatus = ref("Listo para despegar");
const flightProgress = ref(0);
const isFlying = ref(false);
const crashed = ref(false);
const history = ref([]);
const isProcessing = ref(false);

let gameInterval = null;

/* -----------------------------
   CONFIGURAR APUESTA RÁPIDA
----------------------------- */
const setBet = (amount) => {
    betAmount.value = amount;
};

/* -----------------------------
   INICIAR JUEGO
----------------------------- */
const startGame = async () => {
    if (isFlying.value || isProcessing.value) return;
    
    if (betAmount.value <= 0) {
        alert("⚠️ Monto de apuesta inválido");
        return;
    }

    if (betAmount.value > balance.value) {
        alert("⚠️ Fondos insuficientes");
        return;
    }

    isProcessing.value = true;

    try {
        // ✅ Hacer apuesta en el backend
        const response = await axios.post('/games/high-flyer/start', {
            bet: betAmount.value
        });

        if (response.data.success) {
            // ✅ Actualizar el store con el nuevo balance
            authStore.setBalance(response.data.new_balance);
            
            // Iniciar animación
            isFlying.value = true;
            crashed.value = false;
            multiplierStatus.value = "¡Volando!";
            startMultiplier();
        } else {
            alert(response.data.message || "Error al iniciar el juego");
        }
    } catch (error) {
        console.error('Error al iniciar juego:', error);
        alert("Error de conexión. Inténtalo de nuevo.");
    } finally {
        isProcessing.value = false;
    }
};

/* -----------------------------
   CASHOUT (RETIRAR)
----------------------------- */
const cashOut = async () => {
    if (!isFlying.value || isProcessing.value) return;

    isProcessing.value = true;
    stopMultiplier();

    try {
        const winnings = betAmount.value * multiplier.value;

        const response = await axios.post('/games/high-flyer/cashout', {
            multiplier: multiplier.value.toFixed(2),
            winnings: winnings.toFixed(2)
        });

        if (response.data.success) {
            // ✅ Actualizar el store con las ganancias
            authStore.setBalance(response.data.new_balance);
            
            // Agregar al historial
            history.value.unshift({
                value: multiplier.value.toFixed(2),
                crash: false,
                win: true
            });

            multiplierStatus.value = `¡Retiraste a ${multiplier.value.toFixed(2)}x!`;
            
            // Resetear después de 2 segundos
            setTimeout(() => {
                resetGame();
            }, 2000);
        }
    } catch (error) {
        console.error('Error al hacer cashout:', error);
        alert("Error al retirar. Inténtalo de nuevo.");
    } finally {
        isProcessing.value = false;
        isFlying.value = false;
    }
};

/* -----------------------------
   CRASH DEL JUEGO
----------------------------- */
const gameCrashed = async () => {
    stopMultiplier();
    crashed.value = true;
    isFlying.value = false;
    multiplierStatus.value = `💥 CRASH en ${multiplier.value.toFixed(2)}x`;

    try {
        // Notificar al backend del crash
        await axios.post('/games/high-flyer/crash', {
            crash_at: multiplier.value.toFixed(2)
        });

        // Agregar al historial
        history.value.unshift({
            value: multiplier.value.toFixed(2),
            crash: true,
            win: false
        });

        // Resetear después de 3 segundos
        setTimeout(() => {
            resetGame();
        }, 3000);
    } catch (error) {
        console.error('Error al registrar crash:', error);
    }
};

/* -----------------------------
   MULTIPLICADOR ANIMACIÓN
----------------------------- */
const startMultiplier = () => {
    multiplier.value = 1.00;
    flightProgress.value = 0;

    gameInterval = setInterval(() => {
        multiplier.value += 0.01;
        flightProgress.value = Math.min((multiplier.value - 1) * 20, 100);

        // Probabilidad de crash aumenta con el multiplicador
        const crashChance = 0.001 * Math.pow(multiplier.value, 1.5);
        
        if (Math.random() < crashChance) {
            gameCrashed();
        }

        // Crash forzado en 50x
        if (multiplier.value >= 50) {
            gameCrashed();
        }
    }, 100);
};

const stopMultiplier = () => {
    clearInterval(gameInterval);
    gameInterval = null;
};

/* -----------------------------
   RESETEAR JUEGO
----------------------------- */
const resetGame = () => {
    multiplier.value = 1.00;
    flightProgress.value = 0;
    crashed.value = false;
    isFlying.value = false;
    multiplierStatus.value = "Listo para despegar";
};
</script>

<template>
    <AppLayout title="High Flyer">
        <div class="canvas-container">
            <!-- GAME CARD -->
            <div class="game-card p-4">
                <!-- HEADER -->
                <div class="text-center mb-4">
                    <h1 class="display-5 fw-bold">🚀 HIGH FLYER</h1>
                    <p class="text-light-emphasis">Vuela alto, retira a tiempo</p>
                </div>

                <!-- BALANCE -->
                <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded bg-opacity-10 bg-white">
                    <div>
                        <small class="text-light-emphasis">BALANCE</small>
                        <h3 class="mb-0 text-success">S/ {{ typeof balance === 'number' ? balance.toFixed(2) : '0.00' }}</h3>
                    </div>
                </div>

                <!-- MULTIPLIER -->
                <div class="multiplier-display text-center">
                    <small class="text-light-emphasis">MULTIPLICADOR ACTUAL</small>
                    <h1 class="display-2 fw-bold my-2" :class="{ 'text-danger': crashed, 'text-success': isFlying && !crashed }">
                        {{ multiplier.toFixed(2) }}x
                    </h1>
                    <small :class="crashed ? 'text-danger' : 'text-warning'">{{ multiplierStatus }}</small>
                </div>

                <!-- PLANE ANIMATION -->
                <div class="plane-container">
                    <div class="plane" :class="{ 'crash-animation': crashed }" :style="{ left: flightProgress + '%' }">
                        {{ crashed ? '💥' : '✈️' }}
                    </div>
                    <div class="flight-path">
                        <div class="flight-progress" :style="{ width: flightProgress + '%' }"></div>
                    </div>
                </div>

                <!-- BET INPUT -->
                <div class="bet-controls mb-4">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-dark text-white border-dark">S/</span>
                        <input 
                            type="number" 
                            class="form-control bg-dark text-white border-dark"
                            v-model.number="betAmount" 
                            min="1" 
                            :disabled="isFlying"
                        />
                        <button class="btn btn-outline-warning" @click="setBet(10)" :disabled="isFlying">10</button>
                        <button class="btn btn-outline-warning" @click="setBet(50)" :disabled="isFlying">50</button>
                        <button class="btn btn-outline-warning" @click="setBet(100)" :disabled="isFlying">100</button>
                    </div>
                </div>

                <!-- GAME BUTTONS -->
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <button 
                            class="btn btn-glow w-100 h-100" 
                            @click="startGame" 
                            :disabled="isFlying || isProcessing"
                        >
                            {{ isProcessing ? 'PROCESANDO...' : 'DESPEGAR' }}
                        </button>
                    </div>

                    <div class="col-6">
                        <button 
                            class="btn btn-cashout w-100 h-100" 
                            @click="cashOut" 
                            :disabled="!isFlying || crashed || isProcessing"
                        >
                            RETIRAR {{ isFlying ? `(${(betAmount * multiplier).toFixed(2)})` : '' }}
                        </button>
                    </div>
                </div>

                <!-- HISTORY -->
                <div class="history">
                    <h6 class="text-light-emphasis mb-3">📊 Historial de vuelos</h6>
                    <div style="max-height:200px; overflow-y:auto;">
                        <div v-if="history.length === 0" class="text-center text-light-emphasis py-3">
                            <i class="fas fa-plane-departure fa-2x mb-2"></i>
                            <p>Los vuelos aparecerán aquí</p>
                        </div>

                        <div 
                            v-for="(h, i) in history" 
                            :key="i"
                            class="history-entry"
                            :class="{ crash: h.crash, win: h.win }"
                        >
                            <span v-if="h.crash">💥</span>
                            <span v-else>✅</span>
                            {{ h.value }}x
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:root {
    --primary: #6366f1;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --dark: #1e293b;
    --darker: #0f172a;
}

body {
    background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 100%);
    color: white;
    min-height: 100vh;
}

.canvas-container {
    width: 100%;
    max-width: 900px;
    margin: 20px auto;
    padding: 20px;
}

.game-card {
    background: rgba(30, 41, 59, 0.8);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.multiplier-display {
    background: linear-gradient(135deg, #1e40af, #3730a3);
    border-radius: 15px;
    padding: 30px;
    margin: 20px 0;
    position: relative;
    overflow: hidden;
}

.multiplier-display::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    transform: rotate(45deg);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.plane-container {
    position: relative;
    height: 120px;
    margin: 30px 0;
}

.plane {
    font-size: 4rem;
    position: absolute;
    left: 0;
    bottom: 20px;
    transition: left 0.1s ease-out;
    z-index: 2;
}

.plane.crash-animation {
    animation: crash 0.5s ease-out;
}

@keyframes crash {
    0% { transform: rotate(0deg) scale(1); }
    50% { transform: rotate(45deg) scale(1.2); }
    100% { transform: rotate(90deg) scale(0.8); opacity: 0.5; }
}

.flight-path {
    position: absolute;
    bottom: 20px;
    width: 100%;
    height: 4px;
    background: rgba(255,255,255,0.2);
    border-radius: 2px;
}

.flight-progress {
    height: 100%;
    background: linear-gradient(90deg, var(--warning), var(--success));
    transition: width 0.1s ease;
    border-radius: 2px;
}

.btn-glow {
    background: linear-gradient(135deg, var(--primary), #4f46e5);
    border: none;
    border-radius: 12px;
    padding: 15px 30px;
    font-weight: 600;
    color: white;
    transition: all 0.3s;
}

.btn-glow:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
}

.btn-glow:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-cashout {
    background: linear-gradient(135deg, var(--warning), #f97316);
    border: none;
    border-radius: 12px;
    padding: 15px 30px;
    font-weight: 600;
    color: white;
    transition: all 0.3s;
}

.btn-cashout:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.btn-cashout:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.history {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 12px;
    padding: 15px;
}

.history-entry {
    padding: 10px 15px;
    border-radius: 10px;
    margin: 5px 0;
    background: rgba(255,255,255,0.1);
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.history-entry.crash {
    background: rgba(239, 68, 68, 0.1);
    border-left: 4px solid var(--danger);
}

.history-entry.win {
    background: rgba(16, 185, 129, 0.1);
    border-left: 4px solid var(--success);
}

.input-group-text {
    font-weight: bold;
}

.form-control:focus {
    background-color: #0f172a !important;
    color: white !important;
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
}
</style>