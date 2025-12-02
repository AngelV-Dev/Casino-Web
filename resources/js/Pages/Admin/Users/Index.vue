<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    users: Object,
    filters: Object,
    statistics: Object,
    auth: Object,
});

// Estados
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showActionModal = ref(false);
const editingUser = ref(null);
const actionType = ref(null); // 'suspend', 'ban'
const selectedUser = ref(null);

// Usuario actual logueado
const currentUser = computed(() => {
    return props.auth?.user;
});

// --- PERMISOS ---
const canManageUsers = computed(() => {
    return ['super_admin', 'admin'].includes(currentUser.value?.role);
});

// Formularios
const createForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
    initial_balance: 1000,
});

const editForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const actionForm = useForm({
    reason: '',
});

const searchForm = useForm({
    search: props.filters.search || '',
    role: props.filters.role || '',
    status: props.filters.status || '',
});

// Métodos CRUD
function createUser() {
    createForm.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
}

function openEditModal(user) {
    editingUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.password = '';
    editForm.password_confirmation = '';
    showEditModal.value = true;
}

function updateUser() {
    editForm.put(route('admin.users.update', editingUser.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        },
    });
}

function changeRole(user, newRole) {
    if (user.role === 'super_admin') {
        alert('No se puede cambiar el rol del Super Admin');
        return;
    }
    
    if (currentUser.value.role === 'admin' && (newRole === 'admin' || newRole === 'super_admin')) {
        alert('Solo Super Admin puede asignar el rol de Admin');
        return;
    }
    
    if (confirm(`¿Cambiar rol de ${user.name} a ${newRole}?`)) {
        router.put(route('admin.users.update-role', user.id), 
            { role: newRole },
            { preserveScroll: true }
        );
    }
}

function openActionModal(user, action) {
    if (user.role === 'super_admin') {
        alert('No se puede realizar esta acción en el Super Admin');
        return;
    }
    
    if (user.id === currentUser.value.id) {
        alert('No puedes realizar esta acción en tu propia cuenta');
        return;
    }
    
    selectedUser.value = user;
    actionType.value = action;
    actionForm.reason = '';
    showActionModal.value = true;
}

function executeAction() {
    const routes = {
        suspend: route('admin.users.suspend', selectedUser.value.id),
        ban: route('admin.users.ban', selectedUser.value.id),
    };

    actionForm.put(routes[actionType.value], {
        preserveScroll: true,
        onSuccess: () => {
            showActionModal.value = false;
            actionForm.reset();
        },
    });
}

function activateUser(user) {
    if (confirm(`¿Reactivar a ${user.name}?`)) {
        router.put(route('admin.users.activate', user.id), {}, { preserveScroll: true });
    }
}

function deleteUser(user) {
    if (user.role === 'super_admin') {
        alert('No se puede eliminar al Super Admin');
        return;
    }
    
    if (user.id === currentUser.value.id) {
        alert('No puedes eliminar tu propia cuenta');
        return;
    }
    
    if (confirm(`¿ELIMINAR PERMANENTEMENTE a ${user.name}? Esta acción no se puede deshacer.`)) {
        router.delete(route('admin.users.destroy', user.id), { preserveScroll: true });
    }
}

function search() {
    router.get(route('admin.users.index'), searchForm.data(), {
        preserveState: true,
        replace: true,
    });
}

function clearFilters() {
    searchForm.search = '';
    searchForm.role = '';
    searchForm.status = '';
    search();
}

// Helpers visuales
const getRoleBadgeColor = (role) => {
    const colors = {
        super_admin: 'bg-purple-100 text-purple-800',
        admin: 'bg-red-100 text-red-800',
        moderator: 'bg-blue-100 text-blue-800',
        support: 'bg-orange-100 text-orange-800',
        user: 'bg-gray-100 text-gray-800',
    };
    return colors[role] || 'bg-gray-100 text-gray-800';
};

const getStatusBadgeColor = (status) => {
    const colors = {
        active: 'bg-green-100 text-green-800',
        suspended: 'bg-yellow-100 text-yellow-800',
        banned: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getRoleLabel = (role) => {
    const labels = {
        super_admin: 'Super Admin',
        admin: 'Administrador',
        moderator: 'Moderador',
        support: 'Soporte',
        user: 'Usuario',
    };
    return labels[role] || role;
};

const getStatusLabel = (status) => {
    const labels = {
        active: 'Activo',
        suspended: 'Suspendido',
        banned: 'Baneado',
    };
    return labels[status] || status;
};

const actionTitle = computed(() => {
    const titles = {
        suspend: 'Suspender Usuario',
        ban: 'Banear Usuario',
    };
    return titles[actionType.value] || '';
});

const actionButtonText = computed(() => {
    const texts = {
        suspend: 'Suspender',
        ban: 'Banear',
    };
    return texts[actionType.value] || '';
});

const canChangeRole = (user, targetRole) => {
    if (currentUser.value.role === 'super_admin') return true;
    if (currentUser.value.role === 'admin') {
        return !['super_admin', 'admin'].includes(targetRole) && user.role !== 'super_admin';
    }
    return false;
};
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- AQUÍ ESTÁN LOS 6 CUADROS DE ESTADÍSTICAS COMPLETOS -->
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
                    <!-- 1. Total -->
                    <div class="bg-white p-4 rounded-lg shadow border-l-4 border-gray-500">
                        <div class="text-2xl font-bold text-gray-800">{{ statistics.total }}</div>
                        <div class="text-sm text-gray-600">Total</div>
                    </div>
                    <!-- 2. Activos -->
                    <div class="bg-green-50 p-4 rounded-lg shadow border-l-4 border-green-500">
                        <div class="text-2xl font-bold text-green-700">{{ statistics.active }}</div>
                        <div class="text-sm text-green-600">Activos</div>
                    </div>
                    <!-- 3. Suspendidos -->
                    <div class="bg-yellow-50 p-4 rounded-lg shadow border-l-4 border-yellow-500">
                        <div class="text-2xl font-bold text-yellow-700">{{ statistics.suspended }}</div>
                        <div class="text-sm text-yellow-600">Suspendidos</div>
                    </div>
                    <!-- 4. Baneados -->
                    <div class="bg-red-50 p-4 rounded-lg shadow border-l-4 border-red-500">
                        <div class="text-2xl font-bold text-red-700">{{ statistics.banned }}</div>
                        <div class="text-sm text-red-600">Baneados</div>
                    </div>
                    <!-- 5. Admins (Suma SuperAdmin + Admin) -->
                    <div class="bg-purple-50 p-4 rounded-lg shadow border-l-4 border-purple-500">
                        <div class="text-2xl font-bold text-purple-700">{{ (statistics.super_admins || 0) + (statistics.admins || 0) }}</div>
                        <div class="text-sm text-purple-600">Admins</div>
                    </div>
                    <!-- 6. Staff (Suma Mods + Support) -->
                    <div class="bg-blue-50 p-4 rounded-lg shadow border-l-4 border-blue-500">
                        <div class="text-2xl font-bold text-blue-700">{{ (statistics.moderators || 0) + (statistics.supports || 0) }}</div>
                        <div class="text-sm text-blue-600">Staff</div>
                    </div>
                </div>

                <!-- Panel principal -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Gestión de Usuarios</h2>
                            
                            <!-- BOTÓN CREAR: Oculto para Moderadores -->
                            <button 
                                v-if="canManageUsers"
                                @click="showCreateModal = true"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition flex items-center gap-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Crear Usuario
                            </button>
                        </div>

                        <!-- Filtros -->
                        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <input v-model="searchForm.search" @input="search" type="text" placeholder="🔍 Buscar..." class="border border-gray-300 rounded-lg px-4 py-2">
                            <select v-model="searchForm.role" @change="search" class="border border-gray-300 rounded-lg px-4 py-2">
                                <option value="">Todos los roles</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderador</option>
                                <option value="support">Soporte</option>
                                <option value="user">Usuario</option>
                            </select>
                            <select v-model="searchForm.status" @change="search" class="border border-gray-300 rounded-lg px-4 py-2">
                                <option value="">Todos los estados</option>
                                <option value="active">Activo</option>
                                <option value="suspended">Suspendido</option>
                                <option value="banned">Baneado</option>
                            </select>
                            <button @click="clearFilters" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg transition">🔄 Limpiar</button>
                        </div>

                        <!-- Tabla -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-100 border-b-2 border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Usuario</th>
                                        <th class="px-4 py-3 text-left">Rol</th>
                                        <th class="px-4 py-3 text-left">Estado</th>
                                        <th class="px-4 py-3 text-left">Saldo</th>
                                        <th class="px-4 py-3 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">{{ user.name }}</div>
                                            <div class="text-sm text-gray-500">{{ user.email }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <!-- Selector de Rol -->
                                            <select 
                                                v-if="user.role !== 'super_admin' && canChangeRole(user, user.role) && canManageUsers"
                                                :value="user.role"
                                                @change="changeRole(user, $event.target.value)"
                                                class="text-sm border border-gray-300 rounded px-2 py-1"
                                                :class="getRoleBadgeColor(user.role)"
                                            >
                                                <option value="user">Usuario</option>
                                                <option value="support">Soporte</option>
                                                <option value="moderator">Moderador</option>
                                                <option value="admin" v-if="currentUser.role === 'super_admin'">Admin</option>
                                            </select>
                                            <span v-else :class="`px-2 py-1 text-xs font-semibold rounded-full ${getRoleBadgeColor(user.role)}`">
                                                {{ getRoleLabel(user.role) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusBadgeColor(user.status)">
                                                {{ getStatusLabel(user.status) }}
                                            </span>
                                            <div v-if="user.suspension_reason" class="text-xs text-gray-500 mt-1 max-w-xs truncate">
                                                {{ user.suspension_reason }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-green-600">
                                            <!-- Protección de saldo -->
                                            ${{ Number(user.wallet?.balance || 0).toFixed(2) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-center gap-2">
                                                <!-- EDITAR -->
                                                <button v-if="canManageUsers" @click="openEditModal(user)" class="text-blue-600 hover:text-blue-800 text-xl" title="Editar">✏️</button>
                                                
                                                <!-- SUSPENDER -->
                                                <button v-if="user.status === 'active' && user.role !== 'super_admin'" @click="openActionModal(user, 'suspend')" class="text-yellow-600 hover:text-yellow-800 text-xl" title="Suspender">⏸️</button>
                                                
                                                <!-- BANEAR -->
                                                <button v-if="user.status === 'active' && user.role !== 'super_admin'" @click="openActionModal(user, 'ban')" class="text-red-600 hover:text-red-800 text-xl" title="Banear">🚫</button>
                                                
                                                <!-- ACTIVAR -->
                                                <button v-if="user.status !== 'active'" @click="activateUser(user)" class="text-green-600 hover:text-green-800 text-xl" title="Activar">✅</button>
                                                
                                                <!-- ELIMINAR -->
                                                <button v-if="user.role !== 'super_admin' && canManageUsers" @click="deleteUser(user)" class="text-red-600 hover:text-red-800 text-xl" title="Eliminar">🗑️</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginación -->
                        <div class="mt-6 flex justify-center">
                            <div class="flex gap-2">
                                <component 
                                    v-for="(link, index) in users.links" 
                                    :key="index"
                                    :is="link.url ? 'a' : 'span'"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-3 py-2 border rounded-lg text-sm transition"
                                    :class="{
                                        'bg-blue-600 text-white border-blue-600': link.active,
                                        'bg-white text-gray-700 hover:bg-gray-100': !link.active && link.url,
                                        'bg-gray-100 text-gray-400 cursor-not-allowed': !link.url,
                                    }"
                                ></component>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODALES (Se mantienen igual, están al final del archivo) -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="showCreateModal = false">
            <div class="bg-white p-8 rounded-lg w-full max-w-md m-4" @click.stop>
                <h3 class="text-2xl font-bold mb-6">Crear Nuevo Usuario</h3>
                <form @submit.prevent="createUser">
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium mb-1">Nombre</label><input v-model="createForm.name" class="w-full border rounded px-4 py-2"></div>
                        <div><label class="block text-sm font-medium mb-1">Email</label><input v-model="createForm.email" type="email" class="w-full border rounded px-4 py-2"></div>
                        <div><label class="block text-sm font-medium mb-1">Contraseña</label><input v-model="createForm.password" type="password" class="w-full border rounded px-4 py-2"></div>
                        <div><label class="block text-sm font-medium mb-1">Confirmar</label><input v-model="createForm.password_confirmation" type="password" class="w-full border rounded px-4 py-2"></div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Rol</label>
                            <select v-model="createForm.role" class="w-full border rounded px-4 py-2">
                                <option value="user">Usuario</option>
                                <option value="support">Soporte</option>
                                <option value="moderator">Moderador</option>
                                <option value="admin" v-if="currentUser.role === 'super_admin'">Admin</option>
                            </select>
                        </div>
                        <div><label class="block text-sm font-medium mb-1">Saldo Inicial</label><input v-model.number="createForm.initial_balance" type="number" class="w-full border rounded px-4 py-2"></div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded">Crear</button>
                        <button type="button" @click="showCreateModal = false" class="flex-1 bg-gray-300 px-4 py-2 rounded">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="showEditModal = false">
            <div class="bg-white p-8 rounded-lg w-full max-w-md m-4" @click.stop>
                <h3 class="text-2xl font-bold mb-6">Editar Usuario</h3>
                <form @submit.prevent="updateUser">
                    <div class="space-y-4">
                        <div><label class="block text-sm font-medium mb-1">Nombre</label><input v-model="editForm.name" class="w-full border rounded px-4 py-2"></div>
                        <div><label class="block text-sm font-medium mb-1">Email</label><input v-model="editForm.email" type="email" class="w-full border rounded px-4 py-2"></div>
                        <div><label class="block text-sm font-medium mb-1">Nueva Contraseña (Opcional)</label><input v-model="editForm.password" type="password" class="w-full border rounded px-4 py-2"></div>
                        <div v-if="editForm.password"><label class="block text-sm font-medium mb-1">Confirmar</label><input v-model="editForm.password_confirmation" type="password" class="w-full border rounded px-4 py-2"></div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
                        <button type="button" @click="showEditModal = false" class="flex-1 bg-gray-300 px-4 py-2 rounded">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showActionModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="showActionModal = false">
            <div class="bg-white p-8 rounded-lg w-full max-w-md m-4" @click.stop>
                <h3 class="text-2xl font-bold mb-6">{{ actionTitle }}</h3>
                <form @submit.prevent="executeAction">
                    <div>
                        <label class="block text-sm font-medium mb-1">Razón *</label>
                        <textarea v-model="actionForm.reason" required rows="4" class="w-full border rounded px-4 py-2" placeholder="Motivo..."></textarea>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="flex-1 bg-red-600 text-white px-4 py-2 rounded">Confirmar</button>
                        <button type="button" @click="showActionModal = false" class="flex-1 bg-gray-300 px-4 py-2 rounded">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
        
    </AuthenticatedLayout>
</template>