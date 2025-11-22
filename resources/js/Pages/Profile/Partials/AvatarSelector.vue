<script setup>
import { ref } from "vue";

const props = defineProps({
    modelValue: String,
    avatars: Array
});

const emit = defineEmits(["update:modelValue"]);

const selectAvatar = (avatar) => {
    emit("update:modelValue", avatar);
};
</script>

<template>
    <div class="mt-4">
        <h3 class="text-lg mb-2 font-semibold text-white">Elige tu avatar:</h3>

        <div class="grid grid-cols-4 gap-4">
            <div
                v-for="avatar in avatars"
                :key="avatar"
                @click="selectAvatar(avatar)"
                class="cursor-pointer rounded-xl border-2 p-1 transition
                       hover:scale-105 hover:border-green-400"
                :class="{
                    'border-green-500': modelValue === avatar,
                    'border-gray-600': modelValue !== avatar
                }"
            >
                <img :src="`/avatars/${avatar}`" class="rounded-lg w-full" />
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>
