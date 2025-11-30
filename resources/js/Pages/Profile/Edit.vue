<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
// Mantenemos la importación de los componentes hijos
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const page = usePage();
const user = page.props.auth.user;

// Banners según rol
const roleBanners = {
    super_admin: { color: "bg-red-500", label: "SUPER ADMIN" },
    admin: { color: "bg-yellow-500", label: "ADMINISTRADOR" },
    moderator: { color: "bg-purple-500", label: "MODERATOR" },
    support: { color: "bg-blue-500", label: "SUPPORT TEAM" },
    user: { color: "bg-lime-500", label: "MEMBER" },
};

const role = user.role || "user";
const currentBanner = roleBanners[role] ?? roleBanners.user;

// Avatares disponibles
const avatars = [
    'avatar1.png',
    'avatar2.png',
    'avatar3.png',
    'avatar4.png',
    'avatar5.png',
    'avatar6.png',
    'avatar7.png',
    'avatar8.png',
];

// Banners disponibles
const banners = [
    'banner1.jpg',
    'banner2.jpg',
    'banner3.jpg',
    'banner4.jpg',
    'banner5.jpg',
    'banner6.jpg',
];

const selected = ref(user.avatar || null);
const selectedBanner = ref(user.banner || null);
const bio = ref(user.bio || '');

function saveAvatar() {
    router.post('/profile/select-avatar', {
        avatar: selected.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Forzar recarga completa de la página
            window.location.reload();
        }
    });
}

function saveBanner() {
    router.post('/profile/select-banner', {
        banner: selectedBanner.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Forzar recarga completa de la página
            window.location.reload();
        }
    });
}

function saveBio() {
    router.post('/profile/update-bio', {
        bio: bio.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Forzar recarga completa de la página
            window.location.reload();
        }
    });
}

function scrollToAvatarSelector() {
    const section = document.getElementById('avatar-selector');
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}
</script>

<template>
    <Head title="Editar Perfil" />

    <AppLayout>
        <div class="min-h-screen bg-[#0a0b0d] pb-16">
            
            <div class="relative h-64 md:h-72 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-[#1a1d24] to-[#0a0b0d]">
                    <img 
                        v-if="user.banner"
                        :src="`/banners/${user.banner}`" 
                        class="w-full h-full object-cover opacity-30"
                    />
                    <div v-else class="absolute inset-0 bg-[url('/images/derecha.png')] bg-cover bg-center opacity-10"></div>
                </div>
                
                <div class="absolute inset-0 bg-gradient-to-t from-[#0a0b0d] via-transparent to-transparent"></div>

                <div class="relative h-full max-w-7xl mx-auto px-6 md:px-12 flex items-end pb-8">
                    <div class="w-full">
                        <div class="flex flex-col md:flex-row items-center md:items-end gap-6">
                            
                            <div class="relative group flex-shrink-0">
                                <div 
                                    class="absolute -inset-1 rounded-2xl blur-md opacity-75 group-hover:opacity-100 transition bg-lime-500"
                                ></div>
                                <img
                                    :src="`/avatars/${user.avatar || 'avatar_default.png'}`"
                                    class="relative w-32 h-32 md:w-40 md:h-40 rounded-2xl object-cover border-4 border-lime-500 shadow-2xl cursor-pointer transform transition hover:scale-105"
                                    @click="scrollToAvatarSelector"
                                />
                                <div 
                                    @click="scrollToAvatarSelector"
                                    class="absolute inset-0 bg-black/70 rounded-2xl opacity-0 group-hover:opacity-100 transition flex items-center justify-center cursor-pointer"
                                >
                                    <div class="text-center">
                                        <i class="fas fa-camera text-lime-500 text-2xl mb-2"></i>
                                        <p class="text-white text-sm font-semibold">Cambiar</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-1 w-full text-center md:text-left">
                                <div class="flex flex-col md:flex-row md:items-center gap-3 mb-2 justify-center md:justify-start">
                                    <h1 class="text-4xl md:text-5xl font-black text-white drop-shadow-lg">
                                        {{ user.name }}
                                    </h1>
                                    <span 
                                        class="px-4 py-1.5 rounded-full text-black text-sm font-bold uppercase tracking-wider shadow-lg inline-block"
                                        :class="currentBanner.color"
                                    >
                                        {{ currentBanner.label }}
                                    </span>
                                </div>

                                <p class="text-gray-400 text-base mb-2 text-left md:text-left">
                                    {{ user.email }}
                                </p>

                                <p class="text-white text-base md:text-lg font-medium mb-4 text-left md:text-left">
                                    {{ user.bio || '¡Conquistando el casino uno a uno! 🎰' }}
                                </p>

                                <div class="flex flex-wrap justify-center md:justify-start gap-3">
                                    <span class="px-4 py-2 rounded-lg bg-lime-500/10 border border-lime-500/30 text-lime-500 text-sm font-semibold">
                                        <i class="fas fa-check-circle mr-2"></i>Cuenta Verificada
                                    </span>
                                    <span class="px-4 py-2 rounded-lg bg-gray-800/50 border border-gray-700 text-gray-300 text-sm">
                                        <i class="fas fa-calendar mr-2"></i>Miembro desde 2025
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-6 mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-1 space-y-6">
                    
                    <div id="avatar-selector" class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-lime-500 flex items-center gap-2">
                                <i class="fas fa-images"></i>
                                Seleccionar Avatar
                            </h3>
                            <span class="text-2xl">🎨</span>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div
                                v-for="a in avatars"
                                :key="a"
                                @click="selected = a"
                                class="cursor-pointer rounded-xl overflow-hidden border-2 transition transform hover:scale-105"
                                :class="selected === a 
                                    ? 'border-lime-500 shadow-[0_0_20px_rgba(132,204,22,0.6)] scale-105' 
                                    : 'border-gray-700 hover:border-lime-500/50'"
                            >
                                <img :src="`/avatars/${a}`" class="w-full h-24 object-cover" />
                            </div>
                        </div>

                        <button
                            @click="saveAvatar"
                            class="w-full px-4 py-3 rounded-xl bg-lime-500 hover:bg-lime-400 text-black font-bold shadow-lg transition transform hover:scale-105"
                        >
                            <i class="fas fa-save mr-2"></i>Guardar Avatar
                        </button>
                    </div>

                    <div id="banner-selector" class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 shadow-2xl">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-lime-500 flex items-center gap-2">
                                <i class="fas fa-panorama"></i>
                                Seleccionar Banner
                            </h3>
                            <span class="text-2xl">🖼️</span>
                        </div>

                        <div class="grid grid-cols-1 gap-4 mb-6">
                            <div
                                v-for="b in banners"
                                :key="b"
                                @click="selectedBanner = b"
                                class="cursor-pointer rounded-xl overflow-hidden border-2 transition transform hover:scale-105 h-24"
                                :class="selectedBanner === b 
                                    ? 'border-lime-500 shadow-[0_0_20px_rgba(132,204,22,0.6)] scale-105' 
                                    : 'border-gray-700 hover:border-lime-500/50'"
                            >
                                <img :src="`/banners/${b}`" class="w-full h-full object-cover" />
                            </div>
                        </div>

                        <button
                            @click="saveBanner"
                            class="w-full px-4 py-3 rounded-xl bg-lime-500 hover:bg-lime-400 text-black font-bold shadow-lg transition transform hover:scale-105"
                        >
                            <i class="fas fa-save mr-2"></i>Guardar Banner
                        </button>
                    </div>

                </div>

                <div class="lg:col-span-2 space-y-6">

                    <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 md:p-8 shadow-2xl">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center">
                                <i class="fas fa-pen-fancy text-purple-400 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-purple-400">Biografía</h2>
                                <p class="text-gray-400 text-sm">Cuéntale al mundo quién eres</p>
                            </div>
                        </div>
                        
                        <div class="max-w-2xl">
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Descripción
                            </label>
                            <textarea
                                v-model="bio"
                                rows="4"
                                maxlength="200"
                                class="w-full px-4 py-3 bg-black/30 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-lime-500 focus:ring-2 focus:ring-lime-500/20 transition resize-none"
                                placeholder="Ej: ¡Conquistando el casino uno a uno! 🎰"
                            ></textarea>
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-xs text-gray-500">
                                    {{ bio.length }}/200 caracteres
                                </p>
                                <button
                                    @click="saveBio"
                                    class="px-6 py-2 rounded-lg bg-purple-500 hover:bg-purple-400 text-white font-semibold shadow-lg transition transform hover:scale-105"
                                >
                                    <i class="fas fa-save mr-2"></i>Guardar Bio
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 md:p-8 shadow-2xl">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-lime-500/10 border border-lime-500/30 flex items-center justify-center">
                                <i class="fas fa-user-edit text-lime-500 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-lime-500">Información del Perfil</h2>
                                <p class="text-gray-300 text-sm">Actualiza tu nombre y correo electrónico</p>
                            </div>
                        </div>
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-2xl"
                            
                        />
                    </div>

                    <div class="bg-[#1a1d24] border border-gray-800 rounded-2xl p-6 md:p-8 shadow-2xl">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/30 flex items-center justify-center">
                                <i class="fas fa-lock text-blue-400 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-blue-400">Cambiar Contraseña</h2>
                                <p class="text-gray-300 text-sm">Mantén tu cuenta segura con una contraseña fuerte</p>
                            </div>
                        </div>
                        <UpdatePasswordForm class="max-w-2xl" />
                    </div>

                    <div class="bg-[#1a1d24] border border-red-900/50 rounded-2xl p-6 md:p-8 shadow-2xl">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-red-500/10 border border-red-500/30 flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-red-400">Zona de Peligro</h2>
                                <p class="text-gray-300 text-sm">Elimina permanentemente tu cuenta y todos tus datos</p>
                            </div>
                        </div>
                        <DeleteUserForm class="max-w-2xl" />
                    </div>
                </div>

            </div>

            <div class="mt-16 text-center opacity-40 hover:opacity-100 transition-opacity duration-500 cursor-default">
                <p class="text-xs text-gray-500 uppercase tracking-widest">Diseñado por</p>
                <p class="text-sm font-bold text-lime-500 mt-1 font-mono">&lt; iTovarr /&gt;</p>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
* {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

@keyframes shimmer {
    0% { background-position: -1000px 0; }
    100% { background-position: 1000px 0; }
}

.animate-shimmer {
    animation: shimmer 2s infinite;
    background: linear-gradient(to right, transparent 0%, rgba(255,255,255,0.1) 50%, transparent 100%);
    background-size: 1000px 100%;
}

.transition {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}
</style>