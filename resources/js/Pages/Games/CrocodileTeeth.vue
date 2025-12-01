<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    balance: Number,
    user: Object,
});

// ==================== ESTADO DEL JUEGO ====================
const gameState = ref('betting'); // 'betting', 'playing', 'won', 'lost'
const betAmount = ref(10);
const redTeeth = ref(5);
const sessionId = ref(null);
const clickedTeeth = ref([]);
const redPositions = ref([]);
const currentMultiplier = ref(1.0);
const potentialWin = ref(0);
const balance = ref(props.balance);
const isAnimating = ref(false);
const lastClickedTooth = ref(null);

// ==================== CONSTANTES ====================
const totalTeeth = 20;
const minBet = 0.10;
const maxBet = 10000;

// ==================== COMPUTED ====================
const currentBalance = computed(() => {
    const val = Number(balance.value);
    return isNaN(val) ? '0.00' : val.toFixed(2);
});

const nextMultiplier = computed(() => {
    if (gameState.value !== 'playing') return 1.0;
    
    const safeTeethClicked = clickedTeeth.value.length;
    const safeTeeth = totalTeeth - redTeeth.value;
    
    let multiplier = 1.0;
    for (let i = 0; i <= safeTeethClicked; i++) {
        const remaining = totalTeeth - i;
        const safeRemaining = safeTeeth - i;
        if (safeRemaining > 0) {
            multiplier *= remaining / safeRemaining;
        }
    }
    
    return multiplier;
});

const canCashOut = computed(() => {
    return gameState.value === 'playing' && clickedTeeth.value.length > 0;
});

// ==================== MÉTODOS ====================

/**
 * Iniciar nueva partida
 */
async function startGame() {
    if (betAmount.value < minBet || betAmount.value > maxBet) {
        alert(`Apuesta debe estar entre S/${minBet} y S/${maxBet}`);
        return;
    }
    
    if (betAmount.value > balance.value) {
        alert('Saldo insuficiente');
        return;
    }
    
    try {
        const response = await fetch(route('games.crocodile-teeth.start'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                bet_amount: betAmount.value,
                red_teeth: redTeeth.value,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            gameState.value = 'playing';
            sessionId.value = data.data.session_id;
            clickedTeeth.value = [];
            redPositions.value = [];
            currentMultiplier.value = 1.0;
            potentialWin.value = betAmount.value;
            balance.value -= betAmount.value;
        } else {
            alert(data.error || 'Error al iniciar juego');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

/**
 * Click en un diente
 */
async function clickTooth(position) {
    if (gameState.value !== 'playing' || isAnimating.value) return;
    if (clickedTeeth.value.includes(position)) return;
    
    isAnimating.value = true;
    lastClickedTooth.value = position;
    
    try {
        const response = await fetch(route('games.crocodile-teeth.click'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                session_id: sessionId.value,
                tooth_position: position,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            const result = data.data;
            
            if (result.status === 'safe') {
                // Diente seguro
                clickedTeeth.value.push(position);
                currentMultiplier.value = result.current_multiplier;
                potentialWin.value = result.potential_win;
                
                // Animación de éxito
                setTimeout(() => {
                    isAnimating.value = false;
                }, 300);
                
            } else if (result.status === 'lost') {
                // Tocó diente rojo - PERDIÓ
                clickedTeeth.value.push(position);
                redPositions.value = result.red_positions;
                gameState.value = 'lost';
                balance.value = result.new_balance;
                
                // Animación de mordida
                setTimeout(() => {
                    isAnimating.value = false;
                }, 1000);
                
            } else if (result.status === 'max_win') {
                // Victoria máxima
                clickedTeeth.value = result.clicked_teeth;
                gameState.value = 'won';
                potentialWin.value = result.win_amount;
                balance.value = result.new_balance;
                
                setTimeout(() => {
                    isAnimating.value = false;
                }, 1000);
            }
        } else {
            alert(data.error || 'Error al clickear diente');
            isAnimating.value = false;
        }
    } catch (error) {
        console.error('Error:', error);
        // Esto nos dirá si es un error de lectura de JSON (Backend roto)
        alert('Ocurrió un error técnico: ' + error.message); 
        isAnimating.value = false;
    }
}

/**
 * Cash Out (retirar ganancias)
 */
async function cashOut() {
    if (!canCashOut.value) return;
    
    try {
        const response = await fetch(route('games.crocodile-teeth.cashout'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                session_id: sessionId.value,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            gameState.value = 'won';
            potentialWin.value = data.data.win_amount;
            balance.value = data.data.new_balance;
        } else {
            alert(data.error || 'Error al retirar');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

/**
 * Reiniciar juego
 */
function resetGame() {
    gameState.value = 'betting';
    sessionId.value = null;
    clickedTeeth.value = [];
    redPositions.value = [];
    currentMultiplier.value = 1.0;
    potentialWin.value = 0;
    lastClickedTooth.value = null;
}

/**
 * Verificar estado del diente
 */
function getToothState(position) {
    if (gameState.value !== 'playing' && gameState.value !== 'lost' && gameState.value !== 'won') {
        return 'hidden';
    }
    
    if (clickedTeeth.value.includes(position)) {
        if (redPositions.value.includes(position)) {
            return 'red'; // Diente rojo (bomba)
        } else {
            return 'safe'; // Diente seguro (verde)
        }
    }
    
    // Mostrar todos los rojos al perder
    if (gameState.value === 'lost' && redPositions.value.includes(position)) {
        return 'red-revealed';
    }
    
    return 'hidden';
}

// Ajustes de apuesta
function adjustBet(amount) {
    betAmount.value = Math.max(minBet, Math.min(maxBet, betAmount.value + amount));
}

function adjustRedTeeth(amount) {
    redTeeth.value = Math.max(1, Math.min(19, redTeeth.value + amount));
}
</script>

<template>
    <Head title="Crocodile Teeth" />
    
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-gray-900 via-green-900 to-gray-900 py-8">
            <div class="max-w-7xl mx-auto px-4">
                
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-5xl font-bold text-white mb-2">
                        🐊 CROCODILE TEETH 🐊
                    </h1>
                    <p class="text-gray-300">Click en los dientes seguros sin tocar los rojos</p>
                    <div class="mt-4 text-3xl font-bold text-green-400">
                        Saldo: S/{{ currentBalance }}
                    </div>
                </div>

                <!-- Game Area -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left Panel: Controles -->
                    <div class="lg:col-span-1 space-y-4">
                        
                        <!-- Bet Amount -->
                        <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
                            <h3 class="text-white font-semibold mb-3">💰 Apuesta</h3>
                            <div class="flex items-center gap-2 mb-3">
                                <button 
                                    @click="adjustBet(-1)"
                                    :disabled="gameState !== 'betting'"
                                    class="bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white w-10 h-10 rounded-lg transition"
                                >
                                    -
                                </button>
                                <input 
                                    v-model.number="betAmount"
                                    type="number"
                                    step="0.10"
                                    :disabled="gameState !== 'betting'"
                                    class="flex-1 bg-white/10 border-2 border-white/20 rounded-lg px-4 py-2 text-white text-center text-xl font-bold focus:border-green-400 focus:outline-none disabled:opacity-50"
                                >
                                <button 
                                    @click="adjustBet(1)"
                                    :disabled="gameState !== 'betting'"
                                    class="bg-green-500 hover:bg-green-600 disabled:opacity-50 text-white w-10 h-10 rounded-lg transition"
                                >
                                    +
                                </button>
                            </div>
                            <div class="flex gap-2">
                                <button 
                                    v-for="amount in [1, 5, 10, 50, 100]" 
                                    :key="amount"
                                    @click="betAmount = amount"
                                    :disabled="gameState !== 'betting'"
                                    class="flex-1 bg-white/10 hover:bg-white/20 disabled:opacity-50 text-white py-2 rounded-lg text-sm transition"
                                >
                                    {{ amount }}
                                </button>
                            </div>
                        </div>

                        <!-- Red Teeth Selector -->
                        <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
                            <h3 class="text-white font-semibold mb-3">🦷 Dientes Rojos</h3>
                            <div class="flex items-center gap-2 mb-3">
                                <button 
                                    @click="adjustRedTeeth(-1)"
                                    :disabled="gameState !== 'betting'"
                                    class="bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white w-10 h-10 rounded-lg transition"
                                >
                                    -
                                </button>
                                <input 
                                    v-model.number="redTeeth"
                                    type="number"
                                    min="1"
                                    max="19"
                                    :disabled="gameState !== 'betting'"
                                    class="flex-1 bg-white/10 border-2 border-white/20 rounded-lg px-4 py-2 text-white text-center text-xl font-bold focus:border-green-400 focus:outline-none disabled:opacity-50"
                                >
                                <button 
                                    @click="adjustRedTeeth(1)"
                                    :disabled="gameState !== 'betting'"
                                    class="bg-green-500 hover:bg-green-600 disabled:opacity-50 text-white w-10 h-10 rounded-lg transition"
                                >
                                    +
                                </button>
                            </div>
                            <div class="text-sm text-gray-300">
                                Más dientes rojos = Mayor multiplicador
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 border border-white/20">
                            <div class="space-y-3">
                                <div class="flex justify-between text-white">
                                    <span>Multiplicador:</span>
                                    <span class="font-bold text-yellow-400">{{ currentMultiplier.toFixed(2) }}x</span>
                                </div>
                                <div class="flex justify-between text-white">
                                    <span>Siguiente:</span>
                                    <span class="font-bold text-green-400">{{ nextMultiplier.toFixed(2) }}x</span>
                                </div>
                                <div class="flex justify-between text-white">
                                    <span>Ganancia Potencial:</span>
                                    <span class="font-bold text-green-400">S/{{ potentialWin.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-3">
                            <button
                                v-if="gameState === 'betting'"
                                @click="startGame"
                                class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white text-xl font-bold py-4 rounded-xl transition"
                            >
                                🎮 JUGAR
                            </button>

                            <button
                                v-if="canCashOut"
                                @click="cashOut"
                                class="w-full bg-gradient-to-r from-yellow-500 to-orange-600 hover:from-yellow-600 hover:to-orange-700 text-white text-xl font-bold py-4 rounded-xl transition animate-pulse"
                            >
                                💰 RETIRAR S/{{ potentialWin.toFixed(2) }}
                            </button>

                            <button
                                v-if="gameState === 'won' || gameState === 'lost'"
                                @click="resetGame"
                                class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white text-xl font-bold py-4 rounded-xl transition"
                            >
                                🔄 JUGAR DE NUEVO
                            </button>
                        </div>
                    </div>

                    <!-- Center: Crocodile & Teeth -->
                    <div class="lg:col-span-2">
                        <div class="bg-white/5 backdrop-blur-lg rounded-3xl p-8 border border-white/20">
                            
                            <!-- Status Message -->
                            <div v-if="gameState !== 'betting'" class="text-center mb-6">
                                <div 
                                    v-if="gameState === 'playing'"
                                    class="text-2xl font-bold text-yellow-400"
                                >
                                    🎯 Click en un diente seguro
                                </div>
                                <div 
                                    v-if="gameState === 'won'"
                                    class="text-4xl font-bold text-green-400 animate-bounce"
                                >
                                    🎉 ¡GANASTE S/{{ potentialWin.toFixed(2) }}!
                                </div>
                                <div 
                                    v-if="gameState === 'lost'"
                                    class="text-4xl font-bold text-red-400"
                                >
                                    😢 ¡MORDIDO! Perdiste S/{{ betAmount.toFixed(2) }}
                                </div>
                            </div>

                            <!-- Top Row of Teeth -->
                            <div class="grid grid-cols-10 gap-2 mb-4">
                                <button
                                    v-for="i in 10"
                                    :key="i-1"
                                    @click="clickTooth(i-1)"
                                    :disabled="gameState !== 'playing' || isAnimating"
                                    :class="[
                                        'aspect-square rounded-lg transition-all duration-300 text-3xl flex items-center justify-center',
                                        getToothState(i-1) === 'hidden' ? 'bg-white/20 hover:bg-white/30 hover:scale-110' : '',
                                        getToothState(i-1) === 'safe' ? 'bg-green-500 scale-110' : '',
                                        getToothState(i-1) === 'red' ? 'bg-red-500 animate-pulse scale-110' : '',
                                        getToothState(i-1) === 'red-revealed' ? 'bg-red-900/50' : '',
                                        lastClickedTooth === i-1 ? 'ring-4 ring-yellow-400' : '',
                                    ]"
                                >
                                    <span v-if="getToothState(i-1) === 'safe'">✓</span>
                                    <span v-else-if="getToothState(i-1) === 'red' || getToothState(i-1) === 'red-revealed'">✗</span>
                                    <span v-else>🦷</span>
                                </button>
                            </div>

                            <!-- Crocodile -->
                            <div class="flex justify-center my-8">
                                <div 
                                    :class="[
                                        'text-9xl transition-transform duration-500',
                                        gameState === 'lost' ? 'animate-bounce scale-125' : '',
                                    ]"
                                >
                                    {{ gameState === 'lost' ? '😬' : '🐊' }}
                                </div>
                            </div>

                            <!-- Bottom Row of Teeth -->
                            <div class="grid grid-cols-10 gap-2">
                                <button
                                    v-for="i in 10"
                                    :key="i+9"
                                    @click="clickTooth(i+9)"
                                    :disabled="gameState !== 'playing' || isAnimating"
                                    :class="[
                                        'aspect-square rounded-lg transition-all duration-300 text-3xl flex items-center justify-center',
                                        getToothState(i+9) === 'hidden' ? 'bg-white/20 hover:bg-white/30 hover:scale-110' : '',
                                        getToothState(i+9) === 'safe' ? 'bg-green-500 scale-110' : '',
                                        getToothState(i+9) === 'red' ? 'bg-red-500 animate-pulse scale-110' : '',
                                        getToothState(i+9) === 'red-revealed' ? 'bg-red-900/50' : '',
                                        lastClickedTooth === i+9 ? 'ring-4 ring-yellow-400' : '',
                                    ]"
                                >
                                    <span v-if="getToothState(i+9) === 'safe'">✓</span>
                                    <span v-else-if="getToothState(i+9) === 'red' || getToothState(i+9) === 'red-revealed'">✗</span>
                                    <span v-else>🦷</span>
                                </button>
                            </div>
                        </div>

                        <!-- Rules -->
                        <div class="mt-6 bg-white/5 backdrop-blur-lg rounded-xl p-6 text-white">
                            <h3 class="font-bold text-xl mb-3">📋 Reglas del Juego</h3>
                            <ul class="space-y-2 text-sm text-gray-300">
                                <li>• Selecciona la cantidad de dientes rojos (1-19)</li>
                                <li>• Más dientes rojos = Mayor multiplicador = Mayor riesgo</li>
                                <li>• Click en dientes blancos para ganar</li>
                                <li>• Click en diente rojo = Pierdes todo</li>
                                <li>• Puedes retirar en cualquier momento con el botón CASH OUT</li>
                                <li>• Si clickeas todos los blancos sin tocar rojos = Victoria máxima</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>