<template>
  <div class="bg-[#07121a] border border-gray-800 rounded-xl p-4">
    <h3 class="text-sm font-semibold text-neon-green">Amigos</h3>
    <div class="mt-3 space-y-3">
      <div v-for="fr in friends" :key="fr.id" class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <img :src="fr.avatar || '/avatars/avatar_default.png'" class="w-10 h-10 rounded" />
          <div>
            <div class="text-sm">{{ fr.name }}</div>
            <div class="text-xs text-gray-400">{{ fr.last_online || 'Hace días' }}</div>
          </div>
        </div>
        <div class="text-xs text-gray-400">{{ fr.level ? 'Lvl ' + fr.level : '' }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({ friends: { type: Array, default: () => [] }});
</script>
