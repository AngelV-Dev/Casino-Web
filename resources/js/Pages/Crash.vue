<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"

// Props desde Laravel
const props = defineProps({
    balance: Number
})

/* === VARIABLES === */
const balance = ref(Number(props.balance))
const multiplier = ref(1.00)
const isRunning = ref(false)
const isCrashed = ref(false)
const animation = ref(null)

const betAmount = ref("")
const cashoutAt = ref(null)
const crashPoint = ref(null)

const history = ref([])

/* === INICIAR JUEGO === */
const startGame = async () => {
    if (!betAmount.value || betAmount.value <= 0) {
        alert("Monto inválido")
        return
    }

    const res = await axios.post("/crash/start", {
        amount: betAmount.value
    })

    if (!res.data.success) {
        alert(res.data.message)
        return
    }

    balance.value = res.data.new_balance
    crashPoint.value = res.data.crash_at

    // reset
    multiplier.value = 1.00
    isCrashed.value = false
    isRunning.value = true
    cashoutAt.value = null

    animate()
}

/* === ANIMACIÓN === */
const animate = () => {
    animation.value = setInterval(() => {
        multiplier.value += 0.01

        if (multiplier.value >= crashPoint.value) {
            crash()
        }
    }, 50)
}

/* === CASHOUT === */
const doCashout = async () => {
    if (!isRunning.value || isCrashed.value) return

    cashoutAt.value = multiplier.value
    isRunning.value = false

    clearInterval(animation.value)

    const res = await axios.post("/crash/cashout", {
        cashout_at: cashoutAt.value
    })

    if (res.data.success) {
        balance.value = res.data.new_balance
        loadHistory()
    } else {
        alert(res.data.message)
    }
}

/* === CUANDO CRASHEA === */
const crash = () => {
    isCrashed.value = true
    isRunning.value = false
    clearInterval(animation.value)
    loadHistory()
}

/* === HISTORIAL === */
const loadHistory = async () => {
    const res = await axios.get('/crash/history')
    history.value = res.data
}

onMounted(() => loadHistory())
</script>

<template>
<div class="min-h-screen bg-black text-white flex items-center justify-center p-8">
    <div class="w-full max-w-3xl bg-gray-900 rounded-2xl p-8 shadow-lg border border-gray-700">

        <!-- SALDO -->
        <div class="flex justify-between mb-6">
            <h1 class="text-2xl font-bold">Crash Game</h1>
            <div class="text-neon-green text-xl">
                Saldo: S/ {{ balance.toFixed(2) }}
            </div>
        </div>

        <!-- MULTIPLICADOR -->
        <div class="text-center my-10">
            <div class="text-6xl font-extrabold" 
                :class="isCrashed ? 'text-red-500' : 'text-neon-green'">
                {{ multiplier.toFixed(2) }}x
            </div>

            <div v-if="isCrashed" class="text-red-400 text-xl mt-3">
                ¡CRASHED!
            </div>
        </div>

        <!-- CONTROLES -->
        <div class="grid grid-cols-2 gap-6">

            <!-- INPUT DE APUESTA -->
            <div class="flex flex-col">
                <label class="mb-2 text-sm text-gray-300">Monto a apostar</label>
                <input 
                    v-model="betAmount"
                    type="number"
                    class="p-3 rounded bg-gray-800 text-white"
                    :disabled="isRunning"
                    placeholder="Ej: 10"
                />
            </div>

            <!-- BOTONES -->
            <div class="flex items-end gap-4">

                <button 
                    @click="startGame"
                    class="flex-1 py-3 rounded-xl bg-green-500 hover:bg-green-600 transition font-semibold"
                    :disabled="isRunning"
                >
                    Start
                </button>

                <button
                    @click="doCashout"
                    class="flex-1 py-3 rounded-xl bg-yellow-400 hover:bg-yellow-500 text-black font-bold transition"
                    :disabled="!isRunning"
                >
                    Cashout
                </button>
            </div>
        </div>

        <!-- HISTORIAL -->
        <div class="bg-black bg-opacity-40 p-4 rounded-xl border border-gray-800 mt-6">
            <h3 class="text-xl mb-3">Historial</h3>

            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400">
                        <th>Apuesta</th>
                        <th>Crash</th>
                        <th>Cashout</th>
                        <th>Ganancia</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="g in history" :key="g.id" class="border-t border-gray-700">
                        <td>S/ {{ g.bet_amount }}</td>
                        <td>x{{ g.crash_point }}</td>

                        <td v-if="g.cashout_at">x{{ g.cashout_at }}</td>
                        <td v-else>—</td>

                        <td v-if="g.profit" class="text-green-400">
                            +S/ {{ g.profit }}
                        </td>
                        <td v-else class="text-red-400">Perdido</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>
</template>

<style>
.text-neon-green {
    color: #39ff14;
}
</style>
