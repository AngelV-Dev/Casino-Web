<template>
  <div class="w-full">
    <div class="w-full bg-gray-800 h-3 rounded-full overflow-hidden">
      <div :style="{ width: percent + '%' }" class="h-3 bg-gradient-to-r from-neon-green to-green-700 transition-all duration-700"></div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  percent: { type: Number, required: true }
});
</script>

<style scoped>
:root { --neon: #00ff66; }
.bg-neon-green { background: var(--neon); }
</style>
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