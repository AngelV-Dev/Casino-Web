<script setup>
import { ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

const page = usePage()

// Balance dinámico recibido desde Laravel (Inertia shared props)
const balance = ref(page.props.auth.user?.balance ?? 0)

// Formularios
const depositForm = useForm({
    amount: ''  // valor inicial vacío
})

const withdrawForm = useForm({
    amount: ''  // valor inicial vacío
})

// Funciones para depositar
const deposit = () => {
    depositForm.post(route('wallet.deposit'), {
        preserveScroll: true,
        onSuccess: (page) => {
            // Actualizar balance dinámicamente
            balance.value = page.props.auth.user?.balance ?? balance.value
            depositForm.reset()
        }
    })
}

// Funciones para retirar
const withdraw = () => {
    withdrawForm.post(route('wallet.withdraw'), {
        preserveScroll: true,
        onSuccess: (page) => {
            // Actualizar balance dinámicamente
            balance.value = page.props.auth.user?.balance ?? balance.value
            withdrawForm.reset()
        }
    })
}
</script>

<template>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow p-4 text-center">

                    <h3 class="mb-3">💰 Mi Wallet</h3>

                    <h1 class="text-success mb-4">
                        S/ {{ Number(balance).toFixed(2) }}
                    </h1>

                    <!-- Depositar -->
                    <div class="mb-4">
                        <input
                            v-model="depositForm.amount"
                            type="number"
                            min="1"
                            class="form-control mb-2"
                            placeholder="Monto a depositar"
                        >
                        <button
                            @click="deposit"
                            class="btn btn-success w-100"
                            :disabled="depositForm.processing"
                        >
                            Depositar
                        </button>
                    </div>

                    <hr>

                    <!-- Retirar -->
                    <div>
                        <input
                            v-model="withdrawForm.amount"
                            type="number"
                            min="1"
                            class="form-control mb-2"
                            placeholder="Monto a retirar"
                        >
                        <button
                            @click="withdraw"
                            class="btn btn-danger w-100"
                            :disabled="withdrawForm.processing"
                        >
                            Retirar
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
