<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    user: Object,
    balance: [Number, String], // Aceptar ambos tipos
    history: Array
});

// Estado del juego
const betAmount = ref(1.00);
const target = ref(50.00);
const direction = ref('under');
const isPlaying = ref(false);
const diceNumber = ref('?');
const multiplier = ref(1.98);
const winChance = ref(50.00);
const currentBalance = ref(parseFloat(props.balance) || 0); // Convertir a número
const gameHistory = ref(props.history || []);

// Popup de resultado
const showResult = ref(false);
const resultData = ref(null);

// Calcular multiplicador
const calculateMultiplier = () => {
    const chance = direction.value === 'under' ? target.value : (100 - target.value);
    if (chance <= 0) return 0;
    return ((100 / chance) * 0.99).toFixed(2);
};

// Calcular probabilidad de ganar
const calculateWinChance = () => {
    return direction.value === 'under' ? target.value.toFixed(2) : (100 - target.value).toFixed(2);
};

// Actualizar valores cuando cambia el target o dirección
const updateValues = () => {
    multiplier.value = calculateMultiplier();
    winChance.value = calculateWinChance();
};

// Cambiar dirección
const setDirection = (dir) => {
    direction.value = dir;
    updateValues();
};

// Apuestas rápidas
const setBet = (amount) => {
    betAmount.value = amount;
};

const doubleBet = () => {
    betAmount.value = (betAmount.value * 2).toFixed(2);
};

// Ganancia potencial
const potentialWin = computed(() => {
    return (betAmount.value * multiplier.value).toFixed(2);
});

// Jugar
const playDice = async () => {
    if (isPlaying.value) return;

    // Validaciones
    if (betAmount.value < 0.10) {
        alert('La apuesta mínima es S/ 0.10');
        return;
    }

    if (betAmount.value > 10000) {
        alert('La apuesta máxima es S/ 10,000');
        return;
    }

    if (betAmount.value > currentBalance.value) {
        alert('Saldo insuficiente');
        return;
    }

    isPlaying.value = true;
    diceNumber.value = '...';

    try {
        const response = await fetch('/dice/play', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                bet_amount: betAmount.value,
                target: target.value,
                direction: direction.value
            })
        });

        const data = await response.json();

        if (data.success) {
            // Mostrar resultado después de animación
            setTimeout(() => {
                diceNumber.value = data.result.result_number.toFixed(2);
                currentBalance.value = data.result.new_balance;
                
                // Mostrar popup
                resultData.value = data.result;
                showResult.value = true;

                // Agregar al historial
                addToHistory(data.result);
            }, 1000);
        } else {
            alert(data.message || 'Error al procesar la apuesta');
            diceNumber.value = '?';
        }

    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
        diceNumber.value = '?';
    } finally {
        setTimeout(() => {
            isPlaying.value = false;
        }, 1000);
    }
};

// Cerrar resultado
const closeResult = () => {
    showResult.value = false;
};

// Agregar al historial
const addToHistory = (result) => {
    const newBet = {
        bet_amount: betAmount.value,
        target_number: target.value,
        direction: direction.value,
        result_number: result.result_number,
        multiplier: result.multiplier,
        profit: result.profit,
        is_win: result.is_win,
        created_at: new Date().toISOString()
    };
    
    gameHistory.value.unshift(newBet);
    if (gameHistory.value.length > 20) {
        gameHistory.value.pop();
    }
};

// Formatear fecha
const formatDate = (date) => {
    return new Date(date).toLocaleTimeString('es-PE', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};

// Inicializar
onMounted(() => {
    updateValues();
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="game-container">
            <!-- Header -->
            <div class="game-header">
                <h1>🎲 DICE GAME</h1>
                <p>Juego demostrablemente justo</p>
            </div>

            <!-- Balance -->
            <div class="balance-display">
                <h3>Saldo Disponible</h3>
                <div class="amount">S/ {{ currentBalance.toFixed(2) }}</div>
            </div>

            <!-- Área del juego -->
            <div class="game-grid">
                <!-- Zona de juego -->
                <div class="game-area">
                    <div class="dice-display" :class="{ 'dice-rolling': isPlaying }">
                        <div class="dice-number">{{ diceNumber }}</div>
                    </div>

                    <div class="target-slider">
                        <div class="target-display">
                            <div>
                                <div class="label">Número Objetivo</div>
                                <div class="target-value">{{ target.toFixed(2) }}</div>
                            </div>
                            <div>
                                <div class="label">Multiplicador</div>
                                <div class="multiplier-value">{{ multiplier }}x</div>
                            </div>
                        </div>
                        <input 
                            type="range" 
                            v-model="target" 
                            @input="updateValues"
                            min="1.01" 
                            max="98.99" 
                            step="0.01"
                        >
                    </div>

                    <div class="direction-toggle">
                        <button 
                            class="direction-btn"
                            :class="{ active: direction === 'under' }"
                            @click="setDirection('under')"
                        >
                            ⬇ Menor que
                        </button>
                        <button 
                            class="direction-btn"
                            :class="{ active: direction === 'over' }"
                            @click="setDirection('over')"
                        >
                            ⬆ Mayor que
                        </button>
                    </div>

                    <div class="win-chance">
                        <div class="label">Probabilidad de Ganar</div>
                        <div class="value">{{ winChance }}%</div>
                    </div>
                </div>

                <!-- Panel de controles -->
                <div class="controls-panel">
                    <div class="control-group">
                        <label>Cantidad de Apuesta (S/)</label>
                        <input type="number" v-model="betAmount" min="0.10" step="0.10">
                        <div class="quick-bets">
                            <button @click="setBet(0.10)">0.10</button>
                            <button @click="setBet(1.00)">1.00</button>
                            <button @click="setBet(10.00)">10.00</button>
                            <button @click="doubleBet()">2x</button>
                        </div>
                    </div>

                    <div class="control-group">
                        <label>Ganancia Potencial</label>
                        <input type="text" :value="'S/ ' + potentialWin" readonly>
                    </div>

                    <button 
                        class="play-button" 
                        @click="playDice"
                        :disabled="isPlaying"
                    >
                        {{ isPlaying ? '🎲 LANZANDO...' : '🎲 LANZAR DADO' }}
                    </button>

                    <div class="bet-limits">
                        <p>Apuesta mín: S/ 0.10 | Apuesta máx: S/ 10,000</p>
                    </div>
                </div>
            </div>

            <!-- Historial -->
            <div class="history-section">
                <h2>📊 Historial de Apuestas</h2>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Apuesta</th>
                            <th>Objetivo</th>
                            <th>Resultado</th>
                            <th>Mult.</th>
                            <th>Ganancia</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="gameHistory.length === 0">
                            <td colspan="7" style="text-align: center; color: #666;">
                                No hay apuestas aún
                            </td>
                        </tr>
                        <tr v-for="bet in gameHistory" :key="bet.id">
                            <td>{{ formatDate(bet.created_at) }}</td>
                            <td>S/ {{ typeof bet.bet_amount === 'number' ? bet.bet_amount.toFixed(2) : parseFloat(bet.bet_amount || 0).toFixed(2) }}</td>
                            <td>{{ bet.target_number.toFixed(2) }} {{ bet.direction === 'under' ? '⬇' : '⬆' }}</td>
                            <td>{{ bet.result_number.toFixed(2) }}</td>
                            <td>{{ bet.multiplier.toFixed(2) }}x</td>
                            <td :style="{ color: bet.is_win ? '#00ff88' : '#ff4444' }">
                                {{ bet.is_win ? '+' : '' }} S/ {{ bet.profit.toFixed(2) }}
                            </td>
                            <td>
                                <span class="win-badge" :class="{ win: bet.is_win, loss: !bet.is_win }">
                                    {{ bet.is_win ? 'GANÓ' : 'PERDIÓ' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Popup de resultado -->
            <div v-if="showResult" class="result-popup" :class="{ win: resultData?.is_win, loss: !resultData?.is_win }">
                <h2>{{ resultData?.is_win ? '🎉 ¡GANASTE!' : '😢 PERDISTE' }}</h2>
                <div>Resultado: <span class="result-number">{{ resultData?.result_number.toFixed(2) }}</span></div>
                <div 
                    class="profit" 
                    :style="{ color: resultData?.is_win ? '#00ff88' : '#ff4444' }"
                >
                    {{ resultData?.is_win ? '+' : '-' }} S/ {{ Math.abs(resultData?.profit || 0).toFixed(2) }}
                </div>
                <button class="play-button" @click="closeResult">Continuar</button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.game-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    color: #fff;
}

.game-header {
    text-align: center;
    margin-bottom: 30px;
}

.game-header h1 {
    font-size: 2.5rem;
    color: #00ff88;
    margin-bottom: 10px;
}

.balance-display {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    margin-bottom: 30px;
}

.balance-display h3 {
    font-size: 1rem;
    margin-bottom: 5px;
    opacity: 0.9;
}

.balance-display .amount {
    font-size: 2.5rem;
    font-weight: bold;
}

.game-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 20px;
    margin-bottom: 30px;
}

.game-area, .controls-panel {
    background: #242837;
    border-radius: 15px;
    padding: 30px;
}

.dice-display {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 60px;
    text-align: center;
    margin-bottom: 30px;
}

.dice-number {
    font-size: 6rem;
    font-weight: bold;
    text-shadow: 0 0 30px rgba(255,255,255,0.5);
}

.dice-rolling .dice-number {
    animation: roll 0.5s ease-in-out infinite;
}

@keyframes roll {
    0%, 100% { transform: scale(1) rotate(0deg); }
    50% { transform: scale(1.1) rotate(180deg); }
}

.target-slider {
    margin: 30px 0;
}

.target-display {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
}

.label {
    font-size: 0.9rem;
    color: #a0a0a0;
    margin-bottom: 5px;
}

.target-value {
    font-size: 2rem;
    font-weight: bold;
    color: #00ff88;
}

.multiplier-value {
    font-size: 1.5rem;
    color: #ffd700;
}

input[type="range"] {
    width: 100%;
    height: 8px;
    border-radius: 5px;
    background: #1a1d29;
    outline: none;
    -webkit-appearance: none;
}

input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    background: #00ff88;
    cursor: pointer;
    box-shadow: 0 0 10px rgba(0,255,136,0.5);
}

.direction-toggle {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.direction-btn {
    flex: 1;
    padding: 15px;
    border: 2px solid #00ff88;
    background: transparent;
    color: #00ff88;
    border-radius: 10px;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.3s;
}

.direction-btn.active {
    background: #00ff88;
    color: #1a1d29;
}

.win-chance {
    text-align: center;
    padding: 15px;
    background: #1a1d29;
    border-radius: 10px;
}

.win-chance .value {
    font-size: 1.5rem;
    color: #00ff88;
    font-weight: bold;
}

.control-group {
    margin-bottom: 20px;
}

.control-group label {
    display: block;
    margin-bottom: 8px;
    color: #a0a0a0;
    font-size: 0.9rem;
}

.control-group input {
    width: 100%;
    padding: 15px;
    background: #1a1d29;
    border: 2px solid #333;
    border-radius: 10px;
    color: #fff;
    font-size: 1.1rem;
}

.control-group input:focus {
    outline: none;
    border-color: #00ff88;
}

.quick-bets {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-top: 10px;
}

.quick-bets button {
    padding: 10px;
    background: #1a1d29;
    border: 1px solid #333;
    color: #fff;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.quick-bets button:hover {
    background: #00ff88;
    color: #1a1d29;
}

.play-button {
    width: 100%;
    padding: 20px;
    background: linear-gradient(135deg, #00ff88 0%, #00cc6a 100%);
    border: none;
    border-radius: 12px;
    color: #1a1d29;
    font-size: 1.3rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
}

.play-button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0,255,136,0.3);
}

.play-button:disabled {
    background: #333;
    color: #666;
    cursor: not-allowed;
}

.bet-limits {
    text-align: center;
    font-size: 0.85rem;
    color: #666;
    margin-top: 20px;
}

.history-section {
    background: #242837;
    border-radius: 15px;
    padding: 25px;
}

.history-section h2 {
    margin-bottom: 20px;
    color: #00ff88;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
}

.history-table th {
    background: #1a1d29;
    padding: 12px;
    text-align: left;
    color: #a0a0a0;
    font-size: 0.9rem;
}

.history-table td {
    padding: 12px;
    border-bottom: 1px solid #333;
}

.win-badge {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: bold;
}

.win-badge.win {
    background: rgba(0, 255, 136, 0.2);
    color: #00ff88;
}

.win-badge.loss {
    background: rgba(255, 68, 68, 0.2);
    color: #ff4444;
}

.result-popup {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #242837;
    padding: 40px;
    border-radius: 20px;
    text-align: center;
    z-index: 1000;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}

.result-popup.win {
    border: 3px solid #00ff88;
}

.result-popup.loss {
    border: 3px solid #ff4444;
}

.result-popup h2 {
    font-size: 2.5rem;
    margin-bottom: 20px;
}

.result-number {
    font-size: 3rem;
    font-weight: bold;
}

.profit {
    font-size: 2rem;
    font-weight: bold;
    margin: 20px 0;
}

@media (max-width: 968px) {
    .game-grid {
        grid-template-columns: 1fr;
    }
}
</style>