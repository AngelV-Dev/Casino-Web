<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

// Props de Inertia
const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

// Acceder a los datos del usuario desde Inertia
const page = usePage();
const user = page.props.auth.user;

// Banners según rol
const roleBanners = {
    super_admin: {
        bg: "/images/banners/superadmin.png",
        color: "text-red-400",
        label: "SUPER ADMIN"
    },
    admin: {
        bg: "/images/banners/admin.png",
        color: "text-yellow-400",
        label: "ADMINISTRADOR"
    },
    moderator: {
        bg: "/images/banners/mod.png",
        color: "text-purple-400",
        label: "MODERATOR"
    },
    support: {
        bg: "/images/banners/support.png",
        color: "text-blue-400",
        label: "SUPPORT TEAM"
    },
    user: {
        bg: "/images/banners/default.png",
        color: "text-neon-green",
        label: "Member"
    },
};

// Tomar datos del usuario
const role = user.role || "user";
const currentBanner = roleBanners[role] ?? roleBanners.user;

// Avatares disponibles
const avatars = [
    'avatar1.png',
    'avatar2.png',
];

// Avatar seleccionado
const selected = ref(user.avatar || null);

// Guardar avatar
function saveAvatar() {
    router.post('/profile/select-avatar', {
        avatar: selected.value
    });
}

// Scroll al selector
function scrollToAvatarSelector() {
    const section = document.getElementById('avatar-selector');
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>

        <!-- BANNER -->
        <div class="w-full h-56 relative bg-gradient-to-r from-dark-bg to-black shadow-xl">
            <img src="/images/derecha.png"
                class="absolute inset-0 w-full h-full object-cover opacity-40" />

            <div class="absolute inset-0 bg-black/50"></div>

            <div class="relative flex items-center h-full px-10 space-x-6">

                <!-- AVATAR -->
                <img
                    :src="`/avatars/${user.avatar || 'avatar_default.png'}`"
                    class="w-32 h-32 rounded-lg border-2 border-neon-green shadow-lg object-cover cursor-pointer hover:opacity-80 transition"
                    @click="scrollToAvatarSelector"
                />

                <!-- USER INFO -->
                <div>
                    <h1 class="text-4xl font-bold text-neon-green drop-shadow-lg">
                        {{ user.name }}
                    </h1>

                    <p class="text-gray-300 text-lg">
                        {{ user.email }}
                    </p>

                    <div class="mt-3 flex space-x-2">
                        <span class="px-3 py-1 rounded-full bg-neon-green/20 text-neon-green text-sm">
                            Member
                        </span>

                        <span class="px-3 py-1 rounded-full bg-gray-700/50 text-gray-200 text-sm">
                            Verified Account
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SELECTOR DE AVATAR -->
        <div id="avatar-selector" class="bg-black/40 p-6 rounded-xl shadow-lg border border-gray-700">
            <h2 class="text-2xl text-neon-green font-semibold mb-4">Seleccionar Avatar</h2>

            <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-4">
                <div
                    v-for="a in avatars"
                    :key="a"
                    @click="selected = a"
                    class="cursor-pointer rounded-xl overflow-hidden border-2 transition"
                    :class="selected === a ? 'border-neon-green shadow-[0_0_10px_#00FF00]' : 'border-gray-600'"
                >
                    <img :src="`/avatars/${a}`" class="w-full h-20 object-cover" />
                </div>
            </div>

            <button
                @click="saveAvatar"
                class="mt-5 px-4 py-2 rounded-lg bg-gradient-to-r from-neon-green to-darker-green text-black font-semibold hover:opacity-80 transition"
            >
                Guardar Avatar
            </button>
        </div>

        <!-- CONTENIDO -->
        <div class="py-10 bg-dark-bg min-h-screen">
            <div class="mx-auto max-w-6xl grid grid-cols-3 gap-6 px-6">

                <!-- PANEL LATERAL -->
                <div class="col-span-1 space-y-6">

                    <div class="bg-black/40 p-5 rounded-xl shadow-lg border border-gray-700">
                        <h2 class="text-xl font-semibold text-neon-green mb-3">Tu perfil</h2>
                        <p class="text-gray-300 text-sm">
                            Personaliza tu información, actualiza tus datos y mantén tu cuenta segura.
                        </p>
                    </div>

                    <div class="bg-black/40 p-5 rounded-xl shadow-lg border border-gray-700">
                        <h2 class="text-xl text-neon-green font-semibold mb-3">Actividad</h2>

                        <ul class="space-y-2 text-gray-300 text-sm">
                            <li>Último login: <span class="text-neon-green">Hoy</span></li>
                            <li>Miembro desde: <span class="text-neon-green">2025</span></li>
                            <li>Estado: <span class="text-green-400">Online</span></li>
                        </ul>
                    </div>
                </div>

                <!-- PANEL PRINCIPAL -->
                <div class="col-span-2 space-y-6">

                    <div class="bg-black/40 p-6 rounded-xl shadow-lg border border-gray-700">
                        <h2 class="text-2xl text-neon-green font-semibold mb-4">Información del Perfil</h2>
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-2xl"
                        />
                    </div>

                    <div class="bg-black/40 p-6 rounded-xl shadow-lg border border-gray-700">
                        <h2 class="text-2xl text-neon-green font-semibold mb-4">Cambiar Contraseña</h2>
                        <UpdatePasswordForm class="max-w-2xl" />
                    </div>

                    <div class="bg-black/40 p-6 rounded-xl shadow-lg border border-gray-700">
                        <h2 class="text-2xl text-red-400 font-semibold mb-4">Eliminar Cuenta</h2>
                        <DeleteUserForm class="max-w-2xl" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
