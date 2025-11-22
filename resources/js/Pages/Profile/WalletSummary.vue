<template>
  <div class="bg-[#07121a] border border-gray-800 rounded-xl p-4">
    <h3 class="text-sm font-semibold text-neon-green">Billetera</h3>
    <div class="mt-3">
      <div class="text-xl font-bold">{{ user?.wallet?.balance ?? '0.00' }} <span class="text-sm text-gray-400">COINS</span></div>
      <div class="text-xs text-gray-400 mt-1">Última transacción: {{ user?.wallet?.last_transaction_at ?? '—' }}</div>
    </div>
    <div class="mt-3 flex gap-2">
      <button class="px-3 py-1 rounded bg-[#1b2a2d] border border-gray-700 text-sm">Depositar</button>
      <button class="px-3 py-1 rounded bg-neon-green text-black text-sm">Retirar</button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({ user: Object });
</script>
