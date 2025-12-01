// resources/js/stores/authStore.js

import { defineStore } from 'pinia';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

export const useAuthStore = defineStore('auth', () => {
    // Inicializa el saldo con el prop de Inertia (el valor inicial)
    const user = usePage().props.auth.user;
    const balance = ref(Number(user.balance || 0));

    // Acción para establecer el saldo
    const setBalance = (newBalance) => {
        balance.value = Number(newBalance);
    };
    
    // Acción para actualizar el saldo (para juegos o apuestas)
    const updateBalance = (amount) => {
        balance.value += Number(amount); 
    };

    return { balance, setBalance, updateBalance };
});