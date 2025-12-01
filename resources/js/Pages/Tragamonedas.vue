<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import { useAuthStore } from '@/stores/authStore'; // IMPORTAR STORE
import axios from 'axios'; // USAR AXIOS EN LUGAR DE router.post

// ✅ Usar el store en lugar de balance local
const authStore = useAuthStore();

// ✅ Balance reactivo desde el store
const balance = computed(() => authStore.balance);



let gameInterval = null;

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
const autoSpinMax = ref(10); // Máximo de giros automáticos

// Resultado de los rodillos
const reels = ref([
    ['🍒', '🍋', '🍊'],
    ['⭐', '💎', '🎰'],
    ['💰', '7️⃣', '🍒']
]);

// Popup de resultado
const showResult = ref(false);
const resultData = ref(null);

// Apuesta total
const totalBet = computed(() => {
    // Asegurarse de que el cálculo siempre use números
    return (parseFloat(betAmount.value || 0) * lines.value).toFixed(2);
});

// Ganancia potencial (estimado)
const potentialWin = computed(() => {
    return (parseFloat(totalBet.value || 0) * 10).toFixed(2); // Promedio x10
});

// Apuestas rápidas
const setBet = (amount) => {
    betAmount.value = amount;
};

const doubleBet = () => {
    // Evita valores que no son números y asegura el límite superior
    let newBet = parseFloat(betAmount.value || 0) * 2;
    if (newBet > 1000) newBet = 1000;
    betAmount.value = newBet.toFixed(2);
};

const setLines = (count) => {
    lines.value = count;
};

// Función principal de SPIN
const spin = async () => {
    // Si ya está girando, ignorar (importante para evitar múltiples llamadas en auto-spin)
    if (isSpinning.value) return;

    const bet = parseFloat(betAmount.value || 0);
    const total = parseFloat(totalBet.value || 0);

    // Validaciones (Movidas al inicio para detener el flujo inmediatamente)
    if (bet < 0.10) {
        alert('La apuesta mínima es S/ 0.10');
        autoSpin.value = false;
        return;
    }

    if (bet > 1000) {
        alert('La apuesta máxima por línea es S/ 1,000');
        autoSpin.value = false;
        return;
    }

    if (total > currentBalance.value) {
        alert('Saldo insuficiente');
        autoSpin.value = false; // Detener auto-spin si no hay saldo
        return;
    }
    
    // Si se alcanzó el límite de auto-spin, no girar
    if (autoSpin.value && autoSpinCount.value >= autoSpinMax.value) {
        autoSpin.value = false;
        autoSpinCount.value = 0;
        alert('Auto-spin completado: ' + autoSpinMax.value + ' giros');
        return;
    }

    isSpinning.value = true;

    // Animación de giro
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

        authStore.setBalance(data.result.new_balance);

        // **Pausa para la animación visual**
        setTimeout(() => {
            // **Detener la animación del setInterval**
            stopSpinAnimation();

            if (data.success) {
                reels.value = data.result.reels;
                currentBalance.value = parseFloat(data.result.new_balance);
                
                // Mostrar resultado después de un momento
                setTimeout(() => {
                    if (data.result.is_win) {
                        resultData.value = data.result;
                        showResult.value = true;
                        
                        // Si está en auto-spin, cerrar popup automáticamente después de 2 segundos
                        if (autoSpin.value) {
                            setTimeout(() => {
                                showResult.value = false;
                            }, 2000);
                        }
                    }
                    
                    addToHistory(data.result);
                    
                    // **El spin ha terminado: permitir el siguiente giro/botón**
                    isSpinning.value = false;

                    // Lógica de Auto-spin continuo
                    if (autoSpin.value) {
                        autoSpinCount.value++;
                        // Si no se ha alcanzado el límite, programar el siguiente spin
                        if (autoSpinCount.value < autoSpinMax.value) {
                            setTimeout(() => spin(), 1500); // Esperar 1.5s antes del siguiente spin
                        } else {
                            // Se alcanzó el límite: detener auto-spin
                            autoSpin.value = false;
                            autoSpinCount.value = 0;
                            alert('Auto-spin completado: ' + autoSpinMax.value + ' giros');
                        }
                    }
                }, 500);
            } else {
                alert(data.message || 'Error al procesar el spin');
                stopSpinAnimation();
                isSpinning.value = false;
                autoSpin.value = false; // Detener auto-spin si hay error del servidor
            }

        }, 2000); // Duración de la animación visual

    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
        // Asegurar la detención en caso de error de red/servidor
        stopSpinAnimation();
        isSpinning.value = false;
        autoSpin.value = false;
        autoSpinCount.value = 0;
    }
};

// Animación de giro
let spinInterval = null;

const startSpinAnimation = () => {
    stopSpinAnimation();
    
    // Si props.symbols no es un array, usar un array por defecto
    const allSymbols = Array.isArray(props.symbols) ? props.symbols : ['🍒', '🍋', '🍊', '⭐', '💎', '🎰', '💰', '7️⃣'];
    
    if (allSymbols.length === 0) return;
    
    spinInterval = setInterval(() => {
        reels.value = [
            [randomSymbol(allSymbols), randomSymbol(allSymbols), randomSymbol(allSymbols)],
            [randomSymbol(allSymbols), randomSymbol(allSymbols), randomSymbol(allSymbols)],
            [randomSymbol(allSymbols), randomSymbol(allSymbols), randomSymbol(allSymbols)]
        ];
    }, 100);
};

const stopSpinAnimation = () => {
    if (spinInterval) {
        clearInterval(spinInterval);
        spinInterval = null;
    }
};

const randomSymbol = (symbols) => {
    return symbols[Math.floor(Math.random() * symbols.length)];
};

// Auto-spin
const toggleAutoSpin = () => {
    if (!autoSpin.value) {
        autoSpin.value = true;
        autoSpinCount.value = 0; // Reiniciar el contador al activar
        if (!isSpinning.value) {
            spin(); // Iniciar el primer spin si no hay uno en curso
        }
    } else {
        // Detener inmediatamente si ya está activo
        autoSpin.value = false;
        // Si estaba girando, el loop de 'spin' lo detectará al terminar y no continuará
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
        bet_amount: parseFloat(betAmount.value || 0),
        lines: lines.value,
        total_bet: parseFloat(totalBet.value || 0),
        result: result.reels,
        winning_lines: result.winning_lines,
        multiplier: parseFloat(result.multiplier || 0),
        payout: parseFloat(result.payout || 0),
        profit: parseFloat(result.profit || 0),
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
    if (isNaN(num)) return (0).toFixed(decimals);
    return num.toFixed(decimals);
};

// Limpiar al desmontar
onUnmounted(() => {
    stopSpinAnimation();
});
</script>
<template>
<AppLayout title="Tragamonedas">
    <Head title="Slot Machine" />
    
    <div class="game-wrapper">
        <div class="game-container">
            <div class="game-header">
                <h1>🎰 SLOT MACHINE</h1>
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
                        <label for="bet-amount-input">Apuesta por Línea (S/)</label>
                        <input 
                            type="number" 
                            id="bet-amount-input" 
                            v-model.number="betAmount" 
                            min="0.10" 
                            step="0.10" 
                            :disabled="isSpinning"
                            placeholder="Monto de apuesta"
                        >
                        <div class="quick-bets">
                            <button @click="setBet(0.10)" :disabled="isSpinning" aria-label="Establecer apuesta a 0.10">0.10</button>
                            <button @click="setBet(1.00)" :disabled="isSpinning" aria-label="Establecer apuesta a 1.00">1.00</button>
                            <button @click="setBet(10.00)" :disabled="isSpinning" aria-label="Establecer apuesta a 10.00">10.00</button>
                            <button @click="doubleBet()" :disabled="isSpinning" aria-label="Duplicar apuesta">2x</button>
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
                                :aria-label="`Seleccionar ${n} líneas`"
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
                        <span v-else-if="autoSpin">⚡ AUTO-SPIN ACTIVO</span>
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
</AppLayout>
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
    text-shadow: 0 0 30px rgba(255, 215, 0, 0.3);
}

.balance-display {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    border-radius: 15px;
    text-align: center;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
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

.slot-machine {
    background: linear-gradient(135deg, #2d1b3d 0%, #1a1d29 100%);
    border: 4px solid #FFD700;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 0 50px rgba(255, 215, 0, 0.2);
    position: relative;
}

.lines-indicator {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
    padding: 10px;
    background: rgba(0,0,0,0.3);
    border-radius: 10px;
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
    transition: all 0.3s;
}

.line-number.active {
    background: linear-gradient(45deg, #FFD700, #FFA500);
    color: #000;
    box-shadow: 0 0 15px rgba(255, 215, 0, 0.5);
}

.reels-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, #000 0%, #1a1a1a 100%);
    border-radius: 15px;
    box-shadow: inset 0 0 30px rgba(0,0,0,0.8);
}

.reel {
    background: linear-gradient(180deg, #2a2a2a 0%, #1a1a1a 100%);
    border: 3px solid #444;
    border-radius: 10px;
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.symbol {
    background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    font-size: 3rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    transition: all 0.3s;
}

.reels-container.spinning .symbol {
    /* Esta es la animación que se ejecuta mientras isSpinning es TRUE */
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
    font-size: 0.9rem;
}

.payout-symbol {
    font-size: 1.2rem;
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
    border-color: #FFD700;
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

.quick-bets button:hover:not(:disabled) {
    background: #FFD700;
    color: #000;
    transform: scale(1.05);
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
    font-size: 1.1rem;
    font-weight: bold;
    transition: all 0.3s;
}

.lines-selector button:hover:not(:disabled) {
    transform: translateY(-2px);
}

.lines-selector button.active {
    background: linear-gradient(45deg, #FFD700, #FFA500);
    color: #000;
    border-color: #FFD700;
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
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
    font-size: 1.1rem;
}

.info-row:last-child {
    margin-bottom: 0;
}

.value {
    font-weight: bold;
    color: #fff;
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
    transition: all 0.3s;
    box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
    margin-bottom: 10px;
}

.spin-button:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(255, 215, 0, 0.5);
}

.spin-button:disabled {
    background: #333;
    color: #666;
    cursor: not-allowed;
    transform: none;
}

.spin-button.spinning {
    animation: pulse 1s infinite;
}

.spin-button.auto-active {
    background: linear-gradient(135deg, #00ff88 0%, #00cc6a 100%);
    animation: pulse-green 1s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

@keyframes pulse-green {
    0%, 100% { transform: scale(1); box-shadow: 0 10px 30px rgba(0, 255, 136, 0.3); }
    50% { transform: scale(1.02); box-shadow: 0 15px 40px rgba(0, 255, 136, 0.6); }
}

.auto-spin-button {
    width: 100%;
    padding: 15px;
    background: #1a1d29;
    border: 2px solid #FFD700;
    border-radius: 12px;
    color: #FFD700;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 15px;
}

.auto-spin-button:hover {
    background: rgba(255, 215, 0, 0.1);
    transform: translateY(-2px);
}

.auto-spin-button.active {
    background: #FFD700;
    color: #000;
}

.counter {
    font-size: 0.9rem;
    margin-left: 5px;
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
    font-size: 0.9rem;
}

.history-table td {
    padding: 12px;
    border-bottom: 1px solid #333;
}

.result-symbols {
    font-size: 1.5rem;
}

.result-symbols span {
    margin: 0 5px;
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
    box-shadow: 0 20px 60px rgba(0,0,0,0.8);
    border: 4px solid #FFD700;
    animation: popIn 0.3s ease-out;
}

@keyframes popIn {
    0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0; }
    100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
}

.result-popup h2 {
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: #FFD700;
}

.winning-line {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin: 20px 0;
}

.big-symbol {
    font-size: 5rem;
    animation: bounce 0.5s infinite alternate;
}

@keyframes bounce {
    0% { transform: translateY(0); }
    100% { transform: translateY(-10px); }
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