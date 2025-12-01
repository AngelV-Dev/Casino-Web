// ============================================================
// Archivo: resources/js/Components/TicketPanel.vue
// ============================================================

<script setup>
import { ref, onMounted, computed } from 'vue';
// router eliminado porque usas fetch para acciones asíncronas sin recargar

const props = defineProps({
    user: Object,
});

const emit = defineEmits(['close']);

// Estados
const tickets = ref([]);
const selectedTicket = ref(null);
const showCreateModal = ref(false);
const isLoading = ref(false);
const filter = ref('all'); // 'all', 'open', 'in_progress', 'closed'

// Formularios
const createForm = ref({
    subject: '',
    message: '',
});

const replyForm = ref({
    message: '',
});

// Computed
const isStaff = computed(() => {
    return ['super_admin', 'admin', 'moderator', 'support'].includes(props.user?.role);
});

const filteredTickets = computed(() => {
    if (filter.value === 'all') return tickets.value;
    return tickets.value.filter(t => t.status === filter.value);
});

// Helper para obtener CSRF Token de forma segura
const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content;

// Cargar tickets
async function loadTickets() {
    isLoading.value = true;
    try {
        const url = isStaff.value 
            ? route('admin.tickets.index') 
            : route('tickets.index');
            
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        });
        
        const data = await response.json();
        tickets.value = data.tickets;
    } catch (error) {
        console.error('Error cargando tickets:', error);
    } finally {
        isLoading.value = false;
    }
}

// Crear ticket
async function createTicket() {
    if (!createForm.value.subject || !createForm.value.message) {
        alert('Completa todos los campos');
        return;
    }
    
    try {
        const response = await fetch(route('tickets.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify(createForm.value),
        });
        
        const data = await response.json();
        
        if (data.success) {
            createForm.value = { subject: '', message: '' };
            showCreateModal.value = false;
            await loadTickets();
            // Opcional: Mostrar notificación toast aquí
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al crear ticket');
    }
}

// Ver ticket
async function viewTicket(ticket) {
    try {
        const url = isStaff.value 
            ? route('admin.tickets.show', ticket.id)
            : route('tickets.show', ticket.id);
            
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        });
        
        const data = await response.json();
        selectedTicket.value = data.ticket;
    } catch (error) {
        console.error('Error:', error);
    }
}

// Responder ticket
async function replyToTicket() {
    if (!replyForm.value.message) {
        alert('Escribe una respuesta');
        return;
    }
    
    try {
        const url = isStaff.value 
            ? route('admin.tickets.reply', selectedTicket.value.id)
            : route('tickets.reply', selectedTicket.value.id);
            
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify(replyForm.value),
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Agregamos la respuesta localmente para feedback instantáneo
            if (!selectedTicket.value.replies) selectedTicket.value.replies = [];
            selectedTicket.value.replies.push(data.reply);
            
            replyForm.value.message = '';
            // Recargamos en segundo plano para asegurar consistencia
            loadTickets(); 
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al enviar respuesta');
    }
}

// Cerrar ticket
async function closeTicket(ticketId) {
    if (!confirm('¿Cerrar este ticket?')) return;
    
    try {
        const response = await fetch(route('tickets.close', ticketId), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            selectedTicket.value = null;
            await loadTickets();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Cambiar estado (solo staff)
async function changeStatus(ticketId, newStatus) {
    try {
        const response = await fetch(route('admin.tickets.update-status', ticketId), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ status: newStatus }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            selectedTicket.value.status = newStatus;
            await loadTickets();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function getStatusBadge(status) {
    const badges = {
        open: { text: 'Abierto', color: 'bg-green-100 text-green-700' },
        in_progress: { text: 'En Progreso', color: 'bg-yellow-100 text-yellow-700' },
        closed: { text: 'Cerrado', color: 'bg-gray-100 text-gray-700' },
    };
    return badges[status] || badges.open;
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

onMounted(() => {
    loadTickets();
});
</script>

<template>
    <div 
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 transition-opacity"
        @click="emit('close')"
    />

    <div class="fixed right-0 top-0 h-full w-full sm:w-[500px] bg-white shadow-2xl z-50 transform transition-transform overflow-hidden flex flex-col">
        
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 p-6 text-white flex-shrink-0">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <h2 class="text-2xl font-bold">
                        {{ isStaff ? 'Tickets de Soporte' : 'Mis Tickets' }}
                    </h2>
                </div>
                <button
                    @click="emit('close')"
                    class="hover:bg-white/20 p-2 rounded-lg transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <button
                @click="showCreateModal = true"
                class="w-full bg-white/20 hover:bg-white/30 px-4 py-3 rounded-lg flex items-center justify-center gap-2 transition font-semibold"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Nuevo Ticket
            </button>
        </div>

        <div v-if="isStaff" class="flex border-b bg-gray-50 flex-shrink-0">
            <button
                v-for="status in ['all', 'open', 'in_progress', 'closed']"
                :key="status"
                @click="filter = status"
                :class="[
                    'flex-1 py-3 text-sm font-medium transition',
                    filter === status 
                        ? 'border-b-2 border-blue-600 text-blue-600' 
                        : 'text-gray-600 hover:text-gray-800'
                ]"
            >
                {{ status === 'all' ? 'Todos' : status === 'open' ? 'Abiertos' : status === 'in_progress' ? 'En Progreso' : 'Cerrados' }}
            </button>
        </div>

        <div class="flex-1 overflow-y-auto">
            <div v-if="!selectedTicket" class="p-4 space-y-3">
                <div v-if="isLoading" class="text-center py-8 text-gray-500">
                    Cargando...
                </div>

                <div v-else-if="filteredTickets.length === 0" class="text-center py-8 text-gray-500">
                    No hay tickets
                </div>

                <div
                    v-else
                    v-for="ticket in filteredTickets"
                    :key="ticket.id"
                    @click="viewTicket(ticket)"
                    class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition cursor-pointer"
                >
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800">#{{ ticket.id }} - {{ ticket.subject }}</div>
                            <div class="text-sm text-gray-600" v-if="isStaff">Por: {{ ticket.user.name }}</div>
                        </div>
                        <span 
                            :class="`text-xs px-2 py-1 rounded-full font-semibold ${getStatusBadge(ticket.status).color}`"
                        >
                            {{ getStatusBadge(ticket.status).text }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-600 mb-2 line-clamp-2">
                        {{ ticket.message }}
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>{{ formatDate(ticket.created_at) }}</span>
                        <span v-if="ticket.replies?.length">{{ ticket.replies.length }} respuestas</span>
                    </div>
                </div>
            </div>

            <div v-else class="flex flex-col h-full">
                <div class="p-4 border-b bg-gray-50 flex-shrink-0">
                    <button
                        @click="selectedTicket = null"
                        class="text-blue-600 hover:underline text-sm mb-2 flex items-center gap-1"
                    >
                        ← Volver
                    </button>
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-bold text-lg">#{{ selectedTicket.id }} - {{ selectedTicket.subject }}</h3>
                            <p class="text-sm text-gray-600">Por: {{ selectedTicket.user.name }}</p>
                        </div>
                        <span 
                            :class="`text-xs px-3 py-1 rounded-full font-semibold ${getStatusBadge(selectedTicket.status).color}`"
                        >
                            {{ getStatusBadge(selectedTicket.status).text }}
                        </span>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="font-semibold text-gray-800">{{ selectedTicket.user.name }}</div>
                            <span class="text-xs text-gray-500">{{ formatDate(selectedTicket.created_at) }}</span>
                        </div>
                        <p class="text-gray-700">{{ selectedTicket.message }}</p>
                    </div>

                    <div
                        v-for="reply in selectedTicket.replies"
                        :key="reply.id"
                        :class="[
                            'rounded-lg p-4',
                            reply.user.role !== 'user' 
                                ? 'bg-green-50 border border-green-200 ml-4' 
                                : 'bg-gray-50 border border-gray-200'
                        ]"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <div class="font-semibold text-gray-800">{{ reply.user.name }}</div>
                            <span 
                                v-if="reply.user.role !== 'user'"
                                class="text-xs px-2 py-0.5 bg-green-200 text-green-800 rounded-full font-semibold"
                            >
                                Staff
                            </span>
                            <span class="text-xs text-gray-500">{{ formatDate(reply.created_at) }}</span>
                        </div>
                        <p class="text-gray-700">{{ reply.message }}</p>
                    </div>
                </div>

                <div v-if="selectedTicket.status !== 'closed'" class="p-4 border-t bg-gray-50 flex-shrink-0">
                    <textarea
                        v-model="replyForm.message"
                        placeholder="Escribe tu respuesta..."
                        rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    ></textarea>
                    <div class="flex gap-2 mt-2">
                        <button
                            @click="replyToTicket"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition font-semibold"
                        >
                            Enviar Respuesta
                        </button>
                        <button
                            v-if="isStaff"
                            @click="changeStatus(selectedTicket.id, 'closed')"
                            class="px-4 bg-gray-600 hover:bg-gray-700 text-white py-2 rounded-lg transition"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>

                <div v-if="isStaff && selectedTicket.status !== 'closed'" class="px-4 pb-4 flex gap-2 flex-shrink-0">
                    <button
                        v-if="selectedTicket.status === 'open'"
                        @click="changeStatus(selectedTicket.id, 'in_progress')"
                        class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded-lg transition text-sm"
                    >
                        Marcar En Progreso
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="showCreateModal = false">
        <div class="bg-white rounded-lg w-full max-w-md p-6 m-4" @click.stop>
            <h3 class="text-2xl font-bold mb-4">Crear Nuevo Ticket</h3>
            <form @submit.prevent="createTicket">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Asunto</label>
                        <input
                            v-model="createForm.subject"
                            type="text"
                            required
                            maxlength="255"
                            placeholder="Ej: Problema con el pago"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mensaje</label>
                        <textarea
                            v-model="createForm.message"
                            required
                            maxlength="2000"
                            rows="6"
                            placeholder="Describe tu problema o consulta..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        ></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button
                        type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition font-semibold"
                    >
                        Crear Ticket
                    </button>
                    <button
                        type="button"
                        @click="showCreateModal = false"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded-lg transition"
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>