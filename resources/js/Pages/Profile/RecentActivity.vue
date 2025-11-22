<template>
  <div class="bg-[#07121a] border border-gray-800 rounded-xl p-6">
    <h3 class="text-lg font-semibold text-neon-green">Actividad reciente</h3>

    <div class="mt-4 space-y-4">
      <div v-for="act in activities" :key="act.id" class="flex items-start gap-4">
        <div class="w-16 h-12 bg-gray-800 rounded"></div>
        <div>
          <div class="text-sm font-semibold">{{ act.title }}</div>
          <div class="text-xs text-gray-400">{{ act.subtitle }}</div>
        </div>
        <div class="ml-auto text-xs text-gray-500">{{ act.time }}</div>
      </div>

      <div v-if="activities.length === 0" class="text-sm text-gray-400">
        No hay actividad reciente.
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({ activities: { type: Array, default: () => [] }});
</script>
