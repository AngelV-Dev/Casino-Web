<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    balance: [Number, String],
    history: Array,
    symbols: Array,
    payouts: Object
});

// Estado del juego
const betAmount = ref(1.00);
const lines = ref(1);
const isSpinning = ref(false);
const currentBalance = ref(parseFloat(props.balance) || 0);
const gameHistory = ref(props.history || []);
const autoSpin = ref(false);
const autoSpinCount = ref(0);
const autoSpinMax = ref(10);

// Resultado de los rodillos
const reels = ref([
    ['🍒', '🍋', '🍊'],
    ['⭐', '💎', '🎰'],
    ['💰', '7️⃣', '🍒']
]);

// Popup de resultado
const showResult = ref(false);
const resultData = ref(null);

// Variable para el interval (CRÍTICO: solo una instancia)
let spinInterval = null;

// Apuesta total
const totalBet = computed(() => {
    return (parseFloat(betAmount.value) * lines.value).toFixed(2);
});

// Ganancia potencial
const potentialWin = computed(() => {
    return (parseFloat(totalBet.value) * 10).toFixed(2);
});

// Apuestas rápidas
const setBet = (amount) => {
    betAmount.value = amount;
};

const doubleBet = () => {
    betAmount.value = (parseFloat(betAmount.value) * 2).toFixed(2);
};

const setLines = (count) => {
    lines.value = count;
};

// FUNCIÓN CRÍTICA: Detener animación
const stopSpinAnimation = () => {
    console.log('🛑 Deteniendo animación...');
    if (spinInterval) {
        clearInterval(spinInterval);
        spinInterval = null;
        console.log('✅ Animación detenida');
    }
};

// Función para generar símbolo aleatorio
const randomSymbol = (symbols) => {
    return symbols[Math.floor(Math.random() * symbols.length)];
};

// FUNCIÓN CRÍTICA: Iniciar animación
const startSpinAnimation = () => {
    console.log('▶️ Iniciando animación...');
    
    // PRIMERO: Detener cualquier animación previa
    stopSpinAnimation();
    
    const allSymbols = props.symbols;
    
    // SEGUNDO: Crear nueva animación
    spinInterval = setInterval(() => {
        reels.value = [
            [randomSymbol(allSymbols), randomSymbol(allSymbols), randomSymbol(allSymbols)],
            [randomSymbol(allSymbols), randomSymbol(allSymbols), randomSymbol(allSymbols)],
            [randomSymbol(allSymbols), randomSymbol(allSymbols), randomSymbol(allSymbols)]
        ];
    }, 100);
};

// Función principal de SPIN
const spin = async () => {
    console.log('🎰 SPIN LLAMADO', { 
        isSpinning: isSpinning.value, 
        autoSpin: autoSpin.value 
    });

    // BLOQUEO: Si ya está girando, NO hacer nada
    if (isSpinning.value) {
        console.log('⚠️ Ya está girando, bloqueando...');
        return;
    }

    const bet = parseFloat(betAmount.value);
    const total = parseFloat(totalBet.value);

    // Validaciones
    if (bet < 0.10) {
        alert('La apuesta mínima es S/ 0.10');
        return;
    }

    if (bet > 1000) {
        alert('La apuesta máxima por línea es S/ 1,000');
        return;
    }

    if (total > currentBalance.value) {
        alert('Saldo insuficiente');
        autoSpin.value = false;
        autoSpinCount.value = 0;
        return;
    }

    // MARCAR como girando
    isSpinning.value = true;
    console.log('✅ Iniciando giro...');

    // Iniciar animación
    startSpinAnimation();

    try {
        const response = await fetch('/slots/spin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                bet_amount: bet,
                lines: lines.value
            })
        });

        const data = await response.json();

        if (data.success) {
            console.log('✅ Respuesta recibida:', data.result);

            // Esperar 2 segundos para efecto visual
            setTimeout(() => {
                // CRÍTICO: Detener animación INMEDIATAMENTE
                stopSpinAnimation();
                
                // Mostrar resultado real
                reels.value = data.result.reels;
                currentBalance.value = parseFloat(data.result.new_balance);
                
                // Después de 500ms mostrar popup
                setTimeout(() => {
                    if (data.result.is_win) {
                        resultData.value = data.result;
                        showResult.value = true;
                        
                        // Auto-cerrar popup si está en auto-spin
                        if (autoSpin.value) {
                            setTimeout(() => {
                                showResult.value = false;
                            }, 1500);
                        }
                    }
                    
                    // Agregar al historial
                    addToHistory(data.result);
                    
                    // CRÍTICO: DESMARCAR como girando
                    isSpinning.value = false;
                    console.log('🏁 Giro completado');

                    // Auto-spin continuo
                    if (autoSpin.value && autoSpinCount.value < autoSpinMax.value) {
                        autoSpinCount.value++;
                        console.log(`🔄 Auto-spin: ${autoSpinCount.value}/${autoSpinMax.value}`);
                        setTimeout(() => spin(), 1000);
                    } else if (autoSpin.value && autoSpinCount.value >= autoSpinMax.value) {
                        autoSpin.value = false;
                        autoSpinCount.value = 0;
                        console.log('✅ Auto-spin completado');
                        alert('Auto-spin completado: ' + autoSpinMax.value + ' giros');
                    }
                }, 500);
            }, 2000);
        } else {
            console.error('❌ Error del servidor:', data.message);
            alert(data.message || 'Error al procesar el spin');
            stopSpinAnimation();
            isSpinning.value = false;
            autoSpin.value = false;
        }

    } catch (error) {
        console.error('❌ Error de conexión:', error);
        alert('Error de conexión');
        stopSpinAnimation();
        isSpinning.value = false;
        autoSpin.value = false;
        autoSpinCount.value = 0;
    }
};

// Auto-spin toggle
const toggleAutoSpin = () => {
    if (!autoSpin.value) {
        console.log('▶️ Activando auto-spin');
        autoSpin.value = true;
        autoSpinCount.value = 0;
        spin();
    } else {
        console.log('⏸️ Deteniendo auto-spin');
        autoSpin.value = false;
        autoSpinCount.value = 0;
    }
};

// Cerrar resultado
const closeResult = () => {
    showResult.value = false;
};

// Agregar al historial
const addToHistory = (result) => {
    const newBet = {
        id: Date.now(),
        bet_amount: parseFloat(betAmount.value),
        lines: lines.value,
        total_bet: parseFloat(totalBet.value),
        result: result.reels,
        winning_lines: result.winning_lines,
        multiplier: parseFloat(result.multiplier),
        payout: parseFloat(result.payout),
        profit: parseFloat(result.profit),
        is_win: result.is_win,
        created_at: new Date().toISOString()
    };
    
    gameHistory.value.unshift(newBet);
    
    if (gameHistory.value.length > 20) {
        gameHistory.value = gameHistory.value.slice(0, 20);
    }
};

// Formatear fecha
const formatDate = (date) => {
    return new Date(date).toLocaleTimeString('es-PE', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit'
    });
};

// Función segura para formatear números
const safeToFixed = (value, decimals = 2) => {
    const num = typeof value === 'number' ? value : parseFloat(value || 0);
    return num.toFixed(decimals);
};

// CRÍTICO: Limpiar al desmontar
onUnmounted(() => {
    console.log('🧹 Limpiando componente...');
    stopSpinAnimation();
    autoSpin.value = false;
});
</script>

<template>
    <Head title="Tragamonedas" />
    
    <div class="game-wrapper">
        <div class="game-container">
            <div class="game-header">
                <h1>🎰 TRAGAMONEDAS</h1>
                <p>Alinea 3 símbolos iguales para ganar</p>
            </div>

            <div class="balance-display">
                <h3>Saldo Disponible</h3>
                <div class="amount">S/ {{ currentBalance.toFixed(2) }}</div>
            </div>

            <div class="game-grid">
                <div class="slot-machine">
                    <div class="lines-indicator">
                        <div 
                            v-for="n in 9" 
                            :key="n" 
                            class="line-number"
                            :class="{ active: n <= lines }"
                        >
                            {{ n }}
                        </div>
                    </div>

                    <div class="reels-container" :class="{ spinning: isSpinning }">
                        <div 
                            v-for="(reel, reelIndex) in reels" 
                            :key="reelIndex" 
                            class="reel"
                        >
                            <div 
                                v-for="(symbol, symbolIndex) in reel" 
                                :key="symbolIndex" 
                                class="symbol"
                            >
                                {{ symbol }}
                            </div>
                        </div>
                    </div>

                    <div class="paytable">
                        <h3>💰 Tabla de Pagos</h3>
                        <div class="payout-grid">
                            <div v-for="(payout, symbol) in payouts" :key="symbol" class="payout-item">
                                <span class="payout-symbol">{{ symbol }} {{ symbol }} {{ symbol }}</span>
                                <span class="payout-multiplier">{{ payout }}x</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="controls-panel">
                    <div class="control-group">
                        <label>Apuesta por Línea (S/)</label>
                        <input type="number" v-model.number="betAmount" min="0.10" step="0.10" :disabled="isSpinning">
                        <div class="quick-bets">
                            <button @click="setBet(0.10)" :disabled="isSpinning">0.10</button>
                            <button @click="setBet(1.00)" :disabled="isSpinning">1.00</button>
                            <button @click="setBet(10.00)" :disabled="isSpinning">10.00</button>
                            <button @click="doubleBet()" :disabled="isSpinning">2x</button>
                        </div>
                    </div>

                    <div class="control-group">
                        <label>Número de Líneas</label>
                        <div class="lines-selector">
                            <button 
                                v-for="n in [1, 3, 5, 9]" 
                                :key="n"
                                @click="setLines(n)"
                                :class="{ active: lines === n }"
                                :disabled="isSpinning"
                            >
                                {{ n }}
                            </button>
                        </div>
                    </div>

                    <div class="bet-info">
                        <div class="info-row">
                            <span>Apuesta Total:</span>
                            <span class="value">S/ {{ totalBet }}</span>
                        </div>
                        <div class="info-row">
                            <span>Ganancia Máxima:</span>
                            <span class="value gold">S/ {{ potentialWin }}</span>
                        </div>
                    </div>

                    <button 
                        class="spin-button" 
                        @click="spin"
                        :disabled="isSpinning"
                        :class="{ spinning: isSpinning, 'auto-active': autoSpin }"
                    >
                        <span v-if="!isSpinning && !autoSpin">🎰 GIRAR</span>
                        <span v-else-if="autoSpin && !isSpinning">⚡ AUTO-SPIN ACTIVO</span>
                        <span v-else-if="autoSpin && isSpinning">🔄 GIRANDO... ({{ autoSpinCount }}/{{ autoSpinMax }})</span>
                        <span v-else>🔄 GIRANDO...</span>
                    </button>

                    <button 
                        class="auto-spin-button"
                        @click="toggleAutoSpin"
                        :class="{ active: autoSpin }"
                        :disabled="isSpinning && !autoSpin"
                    >
                        <span v-if="!autoSpin">⚡ AUTO-SPIN ({{ autoSpinMax }} giros)</span>
                        <span v-else>⏸️ DETENER AUTO-SPIN ({{ autoSpinCount }}/{{ autoSpinMax }})</span>
                    </button>

                    <div class="bet-limits">
                        <p>Apuesta mín: S/ 0.10 | Apuesta máx: S/ 1,000 por línea</p>
                    </div>
                </div>
            </div>

            <div class="history-section">
                <h2>📊 Historial de Spins</h2>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Apuesta</th>
                            <th>Líneas</th>
                            <th>Resultado</th>
                            <th>Mult.</th>
                            <th>Ganancia</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="gameHistory.length === 0">
                            <td colspan="7" style="text-align: center; color: #666;">
                                No hay spins aún
                            </td>
                        </tr>
                        <tr v-for="bet in gameHistory" :key="bet.id">
                            <td>{{ formatDate(bet.created_at) }}</td>
                            <td>S/ {{ safeToFixed(bet.total_bet) }}</td>
                            <td>{{ bet.lines }}</td>
                            <td class="result-symbols">
                                <span v-for="(reel, i) in bet.result" :key="i">
                                    {{ reel[1] }}
                                </span>
                            </td>
                            <td>{{ safeToFixed(bet.multiplier) }}x</td>
                            <td :style="{ color: bet.is_win ? '#00ff88' : '#ff4444' }">
                                {{ bet.is_win ? '+' : '' }} S/ {{ safeToFixed(bet.profit) }}
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

            <div v-if="showResult" class="result-popup win">
                <h2>🎉 ¡GANASTE!</h2>
                <h2 >❌ PERDISTE</h2>
                <div class="winning-line">
                    <span v-for="(reel, i) in resultData?.reels" :key="i" class="big-symbol">
                        {{ reel[1] }}
                    </span>
                </div>
                <div class="multiplier">{{ safeToFixed(resultData?.multiplier) }}x</div>
                <div class="profit">+ S/ {{ safeToFixed(resultData?.payout) }}</div>
                <button class="spin-button" @click="closeResult">Continuar</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
* {
    box-sizing: border-box;
}

.game-wrapper {
    min-height: 100vh;
    background: linear-gradient(135deg, #1a1d29 0%, #2d1b3d 100%);
    padding: 20px;
}

.game-container {
    max-width: 1400px;
    margin: 0 auto;
    color: #fff;
}

.game-header {
    text-align: center;
    margin-bottom: 30px;
}

.game-header h1 {
    font-size: 3rem;
    background: linear-gradient(45deg, #FFD700, #FFA500);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
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

.slot-machine {
    background: linear-gradient(135deg, #2d1b3d 0%, #1a1d29 100%);
    border: 4px solid #FFD700;
    border-radius: 20px;
    padding: 30px;
}

.lines-indicator {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
}

.line-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #333;
    color: #666;
    font-weight: bold;
}

.line-number.active {
    background: linear-gradient(45deg, #FFD700, #FFA500);
    color: #000;
}

.reels-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 30px;
    padding: 20px;
    background: #000;
    border-radius: 15px;
}

.reel {
    background: #1a1a1a;
    border: 3px solid #444;
    border-radius: 10px;
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.symbol {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    font-size: 3rem;
}

.reels-container.spinning .symbol {
    animation: spin-symbol 0.1s linear infinite;
}

@keyframes spin-symbol {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

.paytable {
    background: rgba(0,0,0,0.3);
    padding: 20px;
    border-radius: 10px;
}

.paytable h3 {
    color: #FFD700;
    margin-bottom: 15px;
    text-align: center;
}

.payout-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.payout-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 12px;
    background: rgba(255,255,255,0.05);
    border-radius: 8px;
}

.payout-multiplier {
    color: #FFD700;
    font-weight: bold;
}

.controls-panel {
    background: #242837;
    border-radius: 15px;
    padding: 25px;
}

.control-group {
    margin-bottom: 20px;
}

.control-group label {
    display: block;
    margin-bottom: 8px;
    color: #a0a0a0;
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
}

.quick-bets button:hover:not(:disabled) {
    background: #FFD700;
    color: #000;
}

.lines-selector {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}

.lines-selector button {
    padding: 15px;
    background: #1a1d29;
    border: 2px solid #333;
    color: #fff;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
}

.lines-selector button.active {
    background: linear-gradient(45deg, #FFD700, #FFA500);
    color: #000;
    border-color: #FFD700;
}

.bet-info {
    background: #1a1d29;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.value {
    font-weight: bold;
}

.value.gold {
    color: #FFD700;
}

.spin-button {
    width: 100%;
    padding: 25px;
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    border: none;
    border-radius: 15px;
    color: #000;
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
    margin-bottom: 10px;
    transition: all 0.3s;
}

.spin-button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(255, 215, 0, 0.5);
}

.spin-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.spin-button.spinning {
    animation: pulse 1s infinite;
}

.spin-button.auto-active {
    background: linear-gradient(135deg, #00ff88 0%, #00cc6a 100%);
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

.auto-spin-button {
    width: 100%;
    padding: 15px;
    background: #1a1d29;
    border: 2px solid #FFD700;
    border-radius: 12px;
    color: #FFD700;
    font-weight: bold;
    cursor: pointer;
    margin-bottom: 15px;
}

.auto-spin-button.active {
    background: #FFD700;
    color: #000;
}

.bet-limits {
    text-align: center;
    font-size: 0.85rem;
    color: #666;
}

.history-section {
    background: #242837;
    border-radius: 15px;
    padding: 25px;
}

.history-section h2 {
    margin-bottom: 20px;
    color: #FFD700;
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
}

.history-table td {
    padding: 12px;
    border-bottom: 1px solid #333;
}

.result-symbols {
    font-size: 1.5rem;
}

.win-badge {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: bold;
}

.win-badge.win {
    background: rgba(255, 215, 0, 0.2);
    color: #FFD700;
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
    background: linear-gradient(135deg, #2d1b3d 0%, #1a1d29 100%);
    padding: 50px;
    border-radius: 20px;
    text-align: center;
    z-index: 1000;
    border: 4px solid #FFD700;
}

.result-popup h2 {
    font-size: 2.5rem;
    color: #FFD700;
    margin-bottom: 20px;
}

.winning-line {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin: 20px 0;
}

.big-symbol {
    font-size: 5rem;
}

.multiplier {
    font-size: 3rem;
    color: #FFD700;
    font-weight: bold;
    margin: 20px 0;
}

.profit {
    font-size: 2.5rem;
    color: #00ff88;
    font-weight: bold;
    margin: 20px 0;
}

@media (max-width: 1200px) {
    .game-grid {
        grid-template-columns: 1fr;
    }
}
</style>