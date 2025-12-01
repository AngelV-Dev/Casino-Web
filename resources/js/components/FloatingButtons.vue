// ============================================================
// Archivo: resources/js/Components/FloatingButtons.vue
// ============================================================

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3'; // Importamos router
import TicketPanel from './TicketPanel.vue';

const { props } = usePage();
const user = computed(() => props.auth.user);

// Estados
const showTicketPanel = ref(false);

// Configuración por rol
const roleConfig = computed(() => {
    const role = user.value?.role;
    
    const configs = {
        super_admin: {
            showUsers: true,
            showTickets: true,
            userIcon: '👑',
            userColor: 'from-purple-600 to-pink-600',
            userLabel: 'Super Admin',
        },
        admin: {
            showUsers: true,
            showTickets: true,
            userIcon: '🛡',
            userColor: 'from-blue-600 to-cyan-600',
            userLabel: 'Admin Panel',
        },
        moderator: {
            showUsers: false,
            showTickets: true,
            userIcon: '⚖',
            userColor: 'from-green-600 to-emerald-600',
            userLabel: 'Moderación',
        },
        support: {
            showUsers: false,
            showTickets: true,
            userIcon: '💬',
            userColor: 'from-orange-600 to-yellow-600',
            userLabel: 'Soporte',
        },
        user: {
            showUsers: false,
            showTickets: true,
            userIcon: '💬',
            userColor: 'from-blue-500 to-blue-600',
            userLabel: 'Soporte',
        },
    };
    
    return configs[role] || configs.user;
});

// Abrir panel de usuarios (redirige usando Inertia Router)
function openUserPanel() {
    router.visit(route('admin.users.index'));
}
</script>

<template>
    <div class="fixed bottom-6 right-6 flex flex-col gap-3 z-50">
        
        <button
            v-if="roleConfig.showTickets"
            @click="showTicketPanel = true"
            class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-full shadow-2xl transition-all hover:scale-110 group relative"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            
            <div class="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-black text-white px-3 py-2 rounded-lg text-sm whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                {{ user?.role === 'user' ? 'Mis Tickets' : 'Tickets de Soporte' }}
            </div>
        </button>

        <button
            v-if="roleConfig.showUsers"
            @click="openUserPanel"
            :class="`bg-gradient-to-r ${roleConfig.userColor} hover:opacity-90 text-white p-4 rounded-full shadow-2xl transition-all hover:scale-110 group relative`"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            
            <div class="absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-black text-white px-3 py-2 rounded-lg text-sm whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                {{ roleConfig.userLabel }}
            </div>
        </button>
    </div>

    <TicketPanel 
        v-if="showTicketPanel"
        :user="user"
        @close="showTicketPanel = false"
    />
</template>