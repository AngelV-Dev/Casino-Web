<script setup>
import { ref, computed, onMounted } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useAuthStore } from '@/stores/authStore';
import axios from 'axios';

const authStore = useAuthStore();
const balance = computed(() => authStore.balance);

// Variables del juego
const betAmount = ref(10);
const multiplier = ref(1.00);
const multiplierStatus = ref("Listo para despegar 🛫");
const flightProgress = ref(0);
const isFlying = ref(false);
const crashed = ref(false);
const history = ref([]);
const isProcessing = ref(false);
const altitude = ref(0);

let gameInterval = null;

// Efectos de partículas
const particles = ref([]);

onMounted(() => {
  createParticles();
});

const createParticles = () => {
  particles.value = Array.from({ length: 15 }, (_, i) => ({
    id: i,
    x: Math.random() * 100,
    y: Math.random() * 100,
    size: Math.random() * 3 + 1,
    speed: Math.random() * 2 + 1
  }));
};

const setBet = (amount) => {
  betAmount.value = amount;
};

const startGame = () => {
  console.log("🎯 DESPEGAR clicked");
  
  if (isFlying.value) {
    console.log("⚠️ Ya está volando");
    return;
  }

  if (betAmount.value > balance.value) {
    alert("⚠️ Fondos insuficientes");
    return;
  }

  // ✅ INICIAR JUEGO DIRECTAMENTE
  isFlying.value = true;
  crashed.value = false;
  multiplierStatus.value = "🛫 Despegando...";
  
  console.log("🚀 Juego iniciado");
  startMultiplier();
};

const cashOut = () => {
  console.log("💰 RETIRAR clicked");
  
  if (!isFlying.value) {
    console.log("⚠️ No hay vuelo activo");
    return;
  }

  stopMultiplier();
  
  const winnings = betAmount.value * multiplier.value;
  
  // ✅ MOSTRAR GANANCIA
  alert(`🎉 ¡Retirado! Ganaste S/ ${winnings.toFixed(2)}`);
  
  // Agregar al historial
  history.value.unshift({
    value: multiplier.value.toFixed(2),
    crash: false,
    win: true,
    profit: winnings
  });

  multiplierStatus.value = `💰 Retirado a ${multiplier.value.toFixed(2)}x!`;
  
  // Resetear juego
  setTimeout(() => {
    resetGame();
  }, 2000);
  
  isFlying.value = false;
};

const gameCrashed = async () => {
  stopMultiplier();
  crashed.value = true;
  isFlying.value = false;
  multiplierStatus.value = `💥 Crash en ${multiplier.value.toFixed(2)}x`;

  try {
    await axios.post('/games/high-flyer/crash', {
      crash_at: multiplier.value.toFixed(2)
    });
    history.value.unshift({
      value: multiplier.value.toFixed(2),
      crash: true,
      win: false,
      profit: -betAmount.value
    });
    showNotification(`💥 Crash en ${multiplier.value.toFixed(2)}x`, "error");
    setTimeout(resetGame, 3000);
  } catch (error) {
    console.error('Error:', error);
  }
};

const startMultiplier = () => {
  multiplier.value = 1.00;
  flightProgress.value = 0;
  altitude.value = 0;

  gameInterval = setInterval(() => {
    multiplier.value += 0.01 + (Math.random() * 0.02);
    flightProgress.value = Math.min((multiplier.value - 1) * 15, 100);
    altitude.value = Math.floor(multiplier.value * 1000);

    // Efectos visuales dinámicos
    updateBackground();

    const crashChance = 0.001 * Math.pow(multiplier.value, 1.8);
    if (Math.random() < crashChance || multiplier.value >= 30) {
      gameCrashed();
    }
  }, 100);
};

const updateBackground = () => {
  const intensity = Math.min(multiplier.value / 10, 1);
  document.documentElement.style.setProperty('--glow-intensity', intensity);
};

const stopMultiplier = () => {
  clearInterval(gameInterval);
  gameInterval = null;
};

const resetGame = () => {
  multiplier.value = 1.00;
  flightProgress.value = 0;
  altitude.value = 0;
  crashed.value = false;
  isFlying.value = false;
  multiplierStatus.value = "Listo para despegar 🛫";
  updateBackground();
};

const showNotification = (message, type = "info") => {
  // Implementar sistema de notificaciones toast
  console.log(`${type}: ${message}`);
};
</script>

<template>
  <AppLayout title="High Flyer">
    <div class="universe">
      <!-- Estrellas de fondo -->
      <div class="stars"></div>
      <div class="stars2"></div>
      <div class="stars3"></div>
      
      <!-- Partículas -->
      <div class="particles">
        <div 
          v-for="particle in particles" 
          :key="particle.id"
          class="particle"
          :style="{
            left: particle.x + '%',
            top: particle.y + '%',
            width: particle.size + 'px',
            height: particle.size + 'px',
            animationDuration: particle.speed + 's'
          }"
        ></div>
      </div>

      <div class="cosmic-container">
        <!-- GAME CARD -->
        <div class="cosmic-card">
          <!-- HEADER GLOW -->
          <div class="header-glow">
            <div class="title-section">
              <h1 class="cosmic-title">
                <span class="title-text">🚀 HIGH FLYER</span>
                <div class="title-glow"></div>
              </h1>
              <p class="cosmic-subtitle">Vuela entre las estrellas • Retira a tiempo</p>
            </div>
          </div>

          <!-- BALANCE CARD -->
          <div class="balance-card">
            <div class="balance-content">
              <div class="balance-info">
                <div class="balance-label">BALANCE TOTAL</div>
                <div class="balance-amount">S/ {{ typeof balance === 'number' ? balance.toFixed(2) : '0.00' }}</div>
              </div>
              <div class="balance-orb">
                <div class="orb-glow"></div>
                <i class="fas fa-wallet"></i>
              </div>
            </div>
          </div>

          <!-- MULTIPLIER NEBULA -->
          <div class="nebula-container">
            <div class="nebula-glow"></div>
            <div class="multiplier-nebula" :class="{ 'pulse-glow': isFlying, 'crash-shake': crashed }">
              <div class="multiplier-content">
                <div class="multiplier-label">MULTIPLICADOR CÓSMICO</div>
                <div class="multiplier-value" :class="{
                  'text-glow': isFlying && !crashed,
                  'text-danger': crashed,
                  'text-success': isFlying && !crashed
                }">
                  {{ multiplier.toFixed(2) }}<span class="multiplier-x">x</span>
                </div>
                <div class="multiplier-status" :class="{
                  'status-flying': isFlying && !crashed,
                  'status-crash': crashed
                }">
                  {{ multiplierStatus }}
                </div>
                <div class="altitude-display" v-if="isFlying">
                  🛰️ Altitud: {{ altitude }} km
                </div>
              </div>
            </div>
          </div>

          <!-- COSMIC FLIGHT AREA -->
          <div class="cosmic-flight">
            <div class="galaxy-path">
              <div class="path-glow"></div>
              <div class="flight-progress" :style="{ width: flightProgress + '%' }"></div>
              
              <!-- Avión con efecto de estela -->
              <div class="plane-with-trail">
                <div class="energy-trail"></div>
                <div class="plane" :class="{ 
                  'flying': isFlying, 
                  'crashing': crashed 
                }" :style="{ left: flightProgress + '%' }">
                  {{ crashed ? '💥' : '🚀' }}
                </div>
              </div>
            </div>
          </div>

          <!-- BET CONTROLS -->
          <div class="quantum-controls">
            <div class="control-group">
              <div class="input-hologram">
                <span class="input-prefix">S/</span>
                <input 
                  type="number" 
                  class="hologram-input"
                  v-model.number="betAmount" 
                  min="1" 
                  :disabled="isFlying"
                  placeholder="0.00"
                />
              </div>
              <div class="quick-bets">
                <button 
                  v-for="amount in [10, 50, 100, 500]" 
                  :key="amount"
                  class="quantum-btn quantum-bet"
                  @click="setBet(amount)"
                  :disabled="isFlying"
                >
                  {{ amount }}
                </button>
              </div>
            </div>
          </div>

          <!-- ACTION BUTTONS -->
          <div class="action-grid">
            <button 
              class="quantum-btn quantum-primary"
              @click="startGame" 
              :disabled="isFlying || isProcessing"
              :class="{ 'btn-pulse': !isFlying && !isProcessing }"
            >
              <div class="btn-content">
                <i class="fas fa-rocket"></i>
                <span>{{ isProcessing ? 'PROCESANDO...' : 'INICIAR VUELO' }}</span>
              </div>
              <div class="btn-glow"></div>
            </button>

            <button 
              class="quantum-btn quantum-success"
              @click="cashOut" 
              :disabled="!isFlying || crashed || isProcessing"
              :class="{ 'btn-glow-success': isFlying }"
            >
              <div class="btn-content">
                <i class="fas fa-money-bill-wave"></i>
                <span>
                  RETIRAR {{ isFlying ? `S/ ${(betAmount * multiplier).toFixed(2)}` : '' }}
                </span>
              </div>
              <div class="btn-glow"></div>
            </button>
          </div>

          <!-- HISTORY TIMELINE -->
          <div class="timeline-container">
            <div class="timeline-header">
              <i class="fas fa-history"></i>
              <span>LINEA TEMPORAL DE VUELOS</span>
            </div>
            <div class="timeline">
              <div v-if="history.length === 0" class="timeline-empty">
                <i class="fas fa-infinity"></i>
                <p>Tu historia cósmica comienza aquí</p>
              </div>
              <div 
                v-for="(record, index) in history.slice(0, 6)" 
                :key="index"
                class="timeline-event"
                :class="{ 'timeline-crash': record.crash, 'timeline-win': record.win }"
              >
                <div class="event-icon">
                  {{ record.crash ? '💥' : '💰' }}
                </div>
                <div class="event-details">
                  <div class="event-multiplier">{{ record.value }}x</div>
                  <div class="event-profit" :class="record.crash ? 'text-danger' : 'text-success'">
                    {{ record.crash ? `-S/ ${Math.abs(record.profit)}` : `+S/ ${record.profit}` }}
                  </div>
                </div>
                <div class="event-time">{{ new Date().toLocaleTimeString() }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
:root {
  --cosmic-primary: #8b5cf6;
  --cosmic-secondary: #06b6d4;
  --cosmic-accent: #10b981;
  --cosmic-danger: #ef4444;
  --cosmic-warning: #f59e0b;
  --cosmic-dark: #0f0f23;
  --cosmic-darker: #070711;
  --cosmic-glow: rgba(139, 92, 246, 0.3);
  --glow-intensity: 0;
}

.universe {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--cosmic-darker) 0%, var(--cosmic-dark) 50%, #1a1a2e 100%);
  position: relative;
  overflow: hidden;
}

/* Estrellas animadas */
.stars, .stars2, .stars3 {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.stars { background: transparent url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="30" r="0.5" fill="white" opacity="0.6"/><circle cx="60" cy="70" r="0.3" fill="white" opacity="0.4"/><circle cx="80" cy="20" r="0.4" fill="white" opacity="0.5"/><circle cx="40" cy="80" r="0.6" fill="white" opacity="0.7"/></svg>') repeat;
  animation: twinkle 8s ease-in-out infinite; }
.stars2 { background: transparent url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="10" cy="40" r="0.4" fill="white" opacity="0.4"/><circle cx="70" cy="10" r="0.3" fill="white" opacity="0.3"/><circle cx="90" cy="60" r="0.5" fill="white" opacity="0.5"/></svg>') repeat;
  animation: twinkle 12s ease-in-out infinite reverse; }
.stars3 { background: transparent url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="30" cy="10" r="0.2" fill="white" opacity="0.3"/><circle cx="50" cy="50" r="0.4" fill="white" opacity="0.4"/></svg>') repeat;
  animation: twinkle 15s ease-in-out infinite; }

@keyframes twinkle {
  0%, 100% { opacity: 0.3; }
  50% { opacity: 1; }
}

/* Partículas */
.particles {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.particle {
  position: absolute;
  background: rgba(139, 92, 246, 0.6);
  border-radius: 50%;
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) scale(1); opacity: 0.6; }
  50% { transform: translateY(-20px) scale(1.2); opacity: 1; }
}

.cosmic-container {
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
  position: relative;
  z-index: 2;
}

.cosmic-card {
  background: rgba(15, 15, 35, 0.7);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(139, 92, 246, 0.2);
  border-radius: 24px;
  padding: 30px;
  box-shadow: 
    0 0 80px rgba(139, 92, 246, calc(0.1 * var(--glow-intensity))),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  position: relative;
  overflow: hidden;
}

.cosmic-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--cosmic-primary), transparent);
  animation: scanline 3s linear infinite;
}

@keyframes scanline {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

/* Header Glow */
.header-glow {
  text-align: center;
  margin-bottom: 30px;
  position: relative;
}

.cosmic-title {
  font-size: 3.5em;
  font-weight: 800;
  background: linear-gradient(135deg, #8b5cf6, #06b6d4, #10b981);
  background-size: 200% 200%;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: gradientShift 3s ease infinite;
  position: relative;
  display: inline-block;
}

@keyframes gradientShift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

.title-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 120%;
  height: 120%;
  background: radial-gradient(circle, rgba(139, 92, 246, 0.3) 0%, transparent 70%);
  filter: blur(20px);
  z-index: -1;
}

.cosmic-subtitle {
  color: rgba(255, 255, 255, 0.6);
  font-size: 1.1em;
  margin-top: 10px;
  font-weight: 300;
}

/* Balance Card */
.balance-card {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(6, 182, 212, 0.1));
  border: 1px solid rgba(139, 92, 246, 0.3);
  border-radius: 20px;
  padding: 20px;
  margin-bottom: 30px;
  position: relative;
  overflow: hidden;
}

.balance-card::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: conic-gradient(transparent, rgba(139, 92, 246, 0.1), transparent 30%);
  animation: rotate 4s linear infinite;
}

@keyframes rotate {
  100% { transform: rotate(360deg); }
}

.balance-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  z-index: 2;
}

.balance-info {
  flex: 1;
}

.balance-label {
  font-size: 0.9em;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 5px;
  font-weight: 300;
}

.balance-amount {
  font-size: 2.5em;
  font-weight: 700;
  background: linear-gradient(135deg, #10b981, #06b6d4);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.balance-orb {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #8b5cf6, #06b6d4);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  box-shadow: 0 0 20px rgba(139, 92, 246, 0.5);
}

.balance-orb i {
  color: white;
  font-size: 1.5em;
}

.orb-glow {
  position: absolute;
  top: -10px;
  left: -10px;
  right: -10px;
  bottom: -10px;
  background: rgba(139, 92, 246, 0.3);
  border-radius: 50%;
  filter: blur(15px);
  animation: pulse 2s ease-in-out infinite;
}

/* Multiplier Nebula */
.nebula-container {
  position: relative;
  margin: 30px 0;
}

.nebula-glow {
  position: absolute;
  top: -20px;
  left: -20px;
  right: -20px;
  bottom: -20px;
  background: radial-gradient(circle, rgba(139, 92, 246, 0.2) 0%, transparent 70%);
  filter: blur(30px);
  opacity: calc(0.5 * var(--glow-intensity));
  transition: opacity 0.3s ease;
}

.multiplier-nebula {
  background: linear-gradient(135deg, rgba(15, 15, 35, 0.9), rgba(30, 30, 60, 0.9));
  border: 1px solid rgba(139, 92, 246, 0.3);
  border-radius: 20px;
  padding: 30px;
  text-align: center;
  position: relative;
  overflow: hidden;
  backdrop-filter: blur(10px);
}

.multiplier-nebula.pulse-glow {
  animation: nebulaPulse 1s ease-in-out infinite;
}

.multiplier-nebula.crash-shake {
  animation: crashShake 0.5s ease-in-out;
}

@keyframes nebulaPulse {
  0%, 100% { box-shadow: 0 0 30px rgba(139, 92, 246, 0.3); }
  50% { box-shadow: 0 0 60px rgba(139, 92, 246, 0.6); }
}

@keyframes crashShake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-10px); }
  75% { transform: translateX(10px); }
}

.multiplier-content {
  position: relative;
  z-index: 2;
}

.multiplier-label {
  font-size: 0.9em;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 10px;
  font-weight: 300;
}

.multiplier-value {
  font-size: 4em;
  font-weight: 800;
  margin: 10px 0;
  background: linear-gradient(135deg, #8b5cf6, #06b6d4);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.multiplier-value.text-glow {
  animation: valueGlow 0.5s ease-in-out infinite alternate;
}

@keyframes valueGlow {
  from { text-shadow: 0 0 10px rgba(139, 92, 246, 0.5); }
  to { text-shadow: 0 0 20px rgba(139, 92, 246, 0.8), 0 0 30px rgba(139, 92, 246, 0.6); }
}

.multiplier-x {
  font-size: 0.6em;
  vertical-align: super;
}

.multiplier-status {
  font-size: 1.1em;
  margin: 10px 0;
  font-weight: 500;
}

.status-flying {
  color: #06b6d4;
  animation: statusPulse 1s ease-in-out infinite;
}

.status-crash {
  color: #ef4444;
  animation: crashFlash 0.5s ease-in-out infinite;
}

@keyframes statusPulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

@keyframes crashFlash {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.altitude-display {
  font-size: 0.9em;
  color: rgba(255, 255, 255, 0.6);
  margin-top: 5px;
}

/* Cosmic Flight Area */
.cosmic-flight {
  margin: 40px 0;
}

.galaxy-path {
  position: relative;
  height: 120px;
  background: linear-gradient(180deg, rgba(15, 15, 35, 0.8), rgba(30, 30, 60, 0.8));
  border: 1px solid rgba(139, 92, 246, 0.2);
  border-radius: 15px;
  overflow: hidden;
}

.path-glow {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(90deg, transparent, rgba(139, 92, 246, 0.1), transparent);
  animation: pathScan 2s linear infinite;
}

@keyframes pathScan {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.flight-progress {
  position: absolute;
  top: 50%;
  left: 0;
  height: 4px;
  background: linear-gradient(90deg, #8b5cf6, #06b6d4, #10b981);
  transform: translateY(-50%);
  transition: width 0.1s ease;
  box-shadow: 0 0 10px rgba(139, 92, 246, 0.5);
}

.plane-with-trail {
  position: absolute;
  top: 50%;
  left: 0;
  transform: translate(-50%, -50%);
  z-index: 3;
}

.energy-trail {
  position: absolute;
  top: 50%;
  right: 100%;
  width: 50px;
  height: 2px;
  background: linear-gradient(90deg, transparent, #8b5cf6);
  filter: blur(2px);
  transform: translateY(-50%);
}

.plane {
  font-size: 3em;
  transition: all 0.1s ease;
  filter: drop-shadow(0 0 10px rgba(139, 92, 246, 0.7));
}

.plane.flying {
  animation: flyingFloat 0.5s ease-in-out infinite alternate;
}

.plane.crashing {
  animation: crashFall 0.5s ease-in-out forwards;
}

@keyframes flyingFloat {
  0% { transform: translateY(-5px); }
  100% { transform: translateY(5px); }
}

@keyframes crashFall {
  0% { transform: rotate(0deg) scale(1); }
  100% { transform: rotate(45deg) translateY(50px) scale(0.8); opacity: 0; }
}

/* Quantum Controls */
.quantum-controls {
  margin: 30px 0;
}

.control-group {
  display: flex;
  gap: 15px;
  align-items: center;
}

.input-hologram {
  flex: 1;
  position: relative;
  background: rgba(15, 15, 35, 0.8);
  border: 1px solid rgba(139, 92, 246, 0.3);
  border-radius: 12px;
  padding: 0 15px;
  display: flex;
  align-items: center;
}

.input-prefix {
  color: rgba(255, 255, 255, 0.7);
  font-weight: 500;
  margin-right: 10px;
}

.hologram-input {
  background: transparent;
  border: none;
  color: white;
  font-size: 1.2em;
  font-weight: 600;
  padding: 15px 0;
  width: 100%;
  outline: none;
}

.hologram-input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.quick-bets {
  display: flex;
  gap: 8px;
}

.quantum-bet {
  padding: 12px 20px;
  font-weight: 600;
}

/* Action Buttons */
.action-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin: 30px 0;
}

.quantum-btn {
  position: relative;
  border: none;
  border-radius: 16px;
  padding: 20px;
  font-size: 1.1em;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.quantum-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

.quantum-primary {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
}

.quantum-primary.btn-pulse {
  animation: primaryPulse 2s ease-in-out infinite;
}

.quantum-success {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.quantum-success.btn-glow-success {
  animation: successGlow 1s ease-in-out infinite alternate;
}

@keyframes primaryPulse {
  0%, 100% { transform: scale(1); box-shadow: 0 0 20px rgba(139, 92, 246, 0.4); }
  50% { transform: scale(1.02); box-shadow: 0 0 30px rgba(139, 92, 246, 0.6); }
}

@keyframes successGlow {
  0% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.4); }
  100% { box-shadow: 0 0 40px rgba(16, 185, 129, 0.8); }
}

.btn-content {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-glow {
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: conic-gradient(transparent, rgba(255, 255, 255, 0.1), transparent);
  animation: btnRotate 3s linear infinite;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.quantum-btn:hover:not(:disabled) .btn-glow {
  opacity: 1;
}

@keyframes btnRotate {
  100% { transform: rotate(360deg); }
}

/* Timeline */
.timeline-container {
  background: rgba(15, 15, 35, 0.6);
  border: 1px solid rgba(139, 92, 246, 0.2);
  border-radius: 16px;
  padding: 20px;
  margin-top: 20px;
}

.timeline-header {
  display: flex;
  align-items: center;
  gap: 10px;
  color: rgba(255, 255, 255, 0.8);
  font-weight: 600;
  margin-bottom: 15px;
  font-size: 0.9em;
}

.timeline {
  max-height: 200px;
  overflow-y: auto;
}

.timeline-empty {
  text-align: center;
  padding: 30px;
  color: rgba(255, 255, 255, 0.5);
}

.timeline-empty i {
  font-size: 2em;
  margin-bottom: 10px;
  opacity: 0.5;
}

.timeline-event {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 12px;
  margin: 8px 0;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  border-left: 4px solid transparent;
  transition: all 0.3s ease;
}

.timeline-event:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateX(5px);
}

.timeline-crash {
  border-left-color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
}

.timeline-win {
  border-left-color: #10b981;
  background: rgba(16, 185, 129, 0.1);
}

.event-icon {
  font-size: 1.5em;
  width: 40px;
  text-align: center;
}

.event-details {
  flex: 1;
}

.event-multiplier {
  font-weight: 600;
  font-size: 1.1em;
}

.event-profit {
  font-size: 0.9em;
  font-weight: 500;
}

.event-time {
  font-size: 0.8em;
  color: rgba(255, 255, 255, 0.5);
}

/* Scrollbar personalizado */
.timeline::-webkit-scrollbar {
  width: 6px;
}

.timeline::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
}

.timeline::-webkit-scrollbar-thumb {
  background: rgba(139, 92, 246, 0.5);
  border-radius: 3px;
}

.timeline::-webkit-scrollbar-thumb:hover {
  background: rgba(139, 92, 246, 0.7);
}
</style>