<template>
  <div class="bg-[#07121a] border border-gray-800 rounded-xl p-4">
    <h3 class="text-sm font-semibold text-neon-green">Logros</h3>
    <div class="mt-3 grid grid-cols-4 gap-3">
      <!-- show first 8 or placeholder -->
      <div v-for="i in 8" :key="i" class="w-full h-12 bg-gray-800 rounded flex items-center justify-center text-xs text-gray-400">
        <span v-if="achievements?.[i-1]">{{ achievements[i-1].title }}</span>
        <span v-else>Sin logro</span>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  user: Object
});
const achievements = props.user?.achievements || [];
</script>
