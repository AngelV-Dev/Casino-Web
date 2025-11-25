<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

/* === RECIBIR DATA REAL DESDE LARAVEL === */
const props = defineProps({
    balance: Number
})

/* convertimos balance a ref para poder editarlo */
const balance = ref(Number(props.balance))
const casinoBalance = ref(0.00)
const sportsBalance = ref(0.00)
const bonusBalance = ref(0.00)

/* inputs */
const depositAmount = ref('')
const withdrawAmount = ref('')

/* métodos de pago */
const paymentMethods = [
  { name: 'PAGOEFECTIVOQR', min: 5, max: 500, image: 'pagoefectivoqr.png' },
  { name: 'NIUBIZ_YAPE', min: 10, max: 500, image: 'niubiz_yape.png' },
  { name: 'NUVEI', min: 5, max: 20000, image: 'nuvei.png' },
  { name: 'TUPAY', min: 20, max: 20000, image: 'tupay.png' }
]

/* === LLAMADAS REALES AL BACKEND === */

/* DEPÓSITO REAL */
const deposit = async () => {
    if (!depositAmount.value || depositAmount.value <= 0) {
        alert("Monto inválido")
        return
    }

    try {
        const res = await axios.post('/wallet/deposit', {
            amount: depositAmount.value
        })

        if (res.data.success) {
            balance.value = Number(res.data.balance)
            depositAmount.value = ''
        } else {
            alert(res.data.message)
        }

    } catch (e) {
        alert(e.response.data.message)
    }
}

/* RETIRO REAL */
const withdraw = async () => {
    if (!withdrawAmount.value || withdrawAmount.value <= 0) {
        alert("Monto inválido")
        return
    }

    try {
        const res = await axios.post('/wallet/withdraw', {
            amount: withdrawAmount.value
        })

        if (res.data.success) {
            balance.value = Number(res.data.balance)
            withdrawAmount.value = ''
        } else {
            alert(res.data.message)
        }

    } catch (e) {
        alert(e.response.data.message)
    }
}

</script>

<template>

<div class="flex h-screen bg-dark-bg text-white font-sans">

    <!-- SIDEBAR -->
    <aside class="w-60 bg-black bg-opacity-40 backdrop-blur-lg p-6 flex flex-col border-r border-gray-800">
        <h2 class="text-2xl font-bold text-neon-green mb-6">MiCasino</h2>

        <nav class="space-y-3">
            <a class="block p-2 rounded hover:bg-gray-800 transition">Inicio</a>
            <a class="block p-2 rounded bg-gray-800 text-neon-green">Wallet</a>
            <a class="block p-2 rounded hover:bg-gray-800 transition">Slots</a>
            <a class="block p-2 rounded hover:bg-gray-800 transition">En Vivo</a>
            <a class="block p-2 rounded hover:bg-gray-800 transition">Promociones</a>
        </nav>
    </aside>

    <!-- CONTENIDO -->
    <main class="flex-1 flex flex-col">

        <!-- HEADER -->
        <header class="p-4 bg-black bg-opacity-30 border-b border-gray-800 flex justify-between items-center">
            <h3 class="text-xl">Mi Billetera</h3>
            <div class="text-lg font-semibold text-neon-green">
                Saldo: S/ {{ balance.toFixed(2) }}
            </div>
        </header>

        <!-- CONTENIDO INTERNO -->
        <div class="overflow-y-auto p-6 space-y-6">

            <!-- TARJETA DE SALDO -->
            <div class="bg-black bg-opacity-40 p-6 rounded-xl shadow-xl border border-gray-800">
                <h4 class="text-center text-lg mb-2">Apuesta Total</h4>

                <h2 class="text-center text-4xl font-bold text-neon-green">
                    S/ {{ balance.toFixed(2) }}
                </h2>

                <div class="grid grid-cols-3 text-center mt-6">
                    <div>
                        <small class="text-placeholder-gray">Casino</small>
                        <p class="text-xl">S/ {{ casinoBalance.toFixed(2) }}</p>
                    </div>
                    <div>
                        <small class="text-placeholder-gray">Deportivas</small>
                        <p class="text-xl">S/ {{ sportsBalance.toFixed(2) }}</p>
                    </div>
                    <div>
                        <small class="text-placeholder-gray">Total Bonos</small>
                        <p class="text-xl">S/ {{ bonusBalance.toFixed(2) }}</p>
                    </div>
                </div>
            </div>

            <!-- ACCIONES -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Depositar -->
                <div class="bg-black bg-opacity-40 p-6 rounded-xl border border-gray-800 shadow-xl space-y-4">
                    <h4 class="text-xl font-semibold">Depositar</h4>

                    <input
                        v-model="depositAmount"
                        type="number"
                        class="w-full px-4 py-3 rounded-lg bg-input-bg text-black placeholder-placeholder-gray focus:outline-none focus:ring-2 focus:ring-neon-green"
                        placeholder="Monto a depositar"
                    />

                    <button
                        @click="deposit"
                        class="w-full py-3 rounded-lg bg-gradient-to-r from-neon-green to-darker-green text-black font-semibold shadow-xl active:scale-95 transition"
                    >
                        Recargar
                    </button>
                </div>

                <!-- Retirar -->
                <div class="bg-black bg-opacity-40 p-6 rounded-xl border border-gray-800 shadow-xl space-y-4">
                    <h4 class="text-xl font-semibold">Retirar</h4>

                    <input
                        v-model="withdrawAmount"
                        type="number"
                        class="w-full px-4 py-3 rounded-lg bg-input-bg text-black placeholder-placeholder-gray focus:outline-none focus:ring-2 focus:ring-neon-green"
                        placeholder="Monto a retirar"
                    />

                    <button
                        @click="withdraw"
                        class="w-full py-3 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold shadow-xl active:scale-95 transition"
                    >
                        Retirar
                    </button>
                </div>

            </div>

            <!-- MÉTODOS DE PAGO -->
            <div class="bg-black bg-opacity-40 p-6 rounded-xl border border-gray-800 shadow-xl">
                <h4 class="text-xl font-semibold mb-4">Métodos de Pago</h4>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div
                        v-for="method in paymentMethods"
                        :key="method.name"
                        class="bg-gray-900 bg-opacity-40 p-4 rounded-xl border border-gray-700 shadow text-center space-y-2"
                    >
                        <img :src="`/images/${method.image}`" class="mx-auto h-16 object-contain" />

                        <p class="font-semibold">{{ method.name }}</p>
                        <small class="text-placeholder-gray block">
                            Min S/ {{ method.min }} — Max S/ {{ method.max }}
                        </small>

                        <button class="w-full py-2 text-sm rounded-lg bg-gradient-to-r from-neon-green to-darker-green text-black font-semibold shadow active:scale-95 transition">
                            Recargar
                        </button>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <footer class="text-center text-placeholder-gray text-sm py-4">
                © 2025 Simulación Apuesta Total | Juego Responsable
            </footer>

        </div>

    </main>

</div>

</template>
<!-- 
==========================================
                   __
                  / _)
         _/\/\/\_/ /
       _|         /
     _|   ( |  ( |
    /__.-'|_|--|_| Tovar
 ==========================================
-->