<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="Casino Home" />
    <div class="bg-[#121415] min-h-screen flex text-white font-sans">

        <aside class="w-64 bg-[#1e2021] flex flex-col p-4 shadow-xl">
            <div class="mb-8">
                <div class="text-2xl font-bold text-white">Logo Casino</div>
            </div>
            <nav class="flex-1 space-y-2">
                <a href="#" class="flex items-center p-3 rounded-lg text-white/70 hover:bg-[#343a3d] hover:text-white transition duration-200">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-10v10a1 1 0 001 1h3M7 21h10"></path></svg>
                    Home
                </a>
                <a href="#" class="flex items-center p-3 rounded-lg text-white/70 bg-[#343a3d] hover:text-white transition duration-200">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3 .895 3 2s-1.343 2-3 2-3 .895-3 2 1.343 2 3 2m-3-2V8m6 0v4m-3 4v-4m-3-4h6m-6 0h6m-6 4h6m-6 0h6m-6 4h6m-6 0h6"></path></svg>
                    Slots
                </a>
                <a href="#" class="flex items-center p-3 rounded-lg text-white/70 hover:bg-[#343a3d] hover:text-white transition duration-200">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Jackpots
                </a>
                <a href="#" class="flex items-center p-3 rounded-lg text-white/70 hover:bg-[#343a3d] hover:text-white transition duration-200">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h-2m2 0h2m-4 0v2m0-2v-2m4 2v2m0-2v-2M5 12h2m0 0h2m-4 0v2m0-2v-2m4 2v2m0-2v-2"></path></svg>
                    Crash Games
                </a>
                </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">

            <header class="bg-[#121415] shadow-md p-4 flex justify-between items-center z-10">
                <div class="relative flex items-center w-64 mr-4">
                    <input
                        type="text"
                        placeholder="Buscar..."
                        class="w-full bg-[#1e2021] text-white/90 rounded-md py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-[#00cc66]"
                    />
                    <svg class="w-5 h-5 text-white/70 absolute left-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <div class="flex items-center space-x-4">
                    <select class="bg-[#121415] text-white/70 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-[#00cc66] appearance-none">
                        <option value="es">ES</option>
                        <option value="en">EN</option>
                    </select>

                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="rounded-md px-4 py-2 text-white ring-1 ring-[#00cc66] transition hover:bg-[#00cc66] focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                    >
                        Iniciar Sesión
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-md px-4 py-2 text-white bg-[#00cc66] transition hover:bg-[#00a352] focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                    >
                        Registrarse
                    </Link>

                    <div v-if="$page.props.auth.user" class="rounded-full bg-white/20 p-2">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">

                <div class="mb-8 rounded-xl overflow-hidden shadow-2xl relative h-64 bg-gradient-to-r from-[#202224] to-[#4b143c]">
                    <div class="absolute inset-0 flex items-center p-8">
                        <div class="max-w-md">
                            <span class="text-[#00cc66] font-bold text-sm uppercase">JUEGO NUEVO</span>
                            <h2 class="text-3xl font-extrabold mt-1 mb-3">JUEGA SWEET RUSH BONANZA Y DEJA QUE LOS CARAMELOS TE PREMIEN</h2>
                            <button class="bg-[#00cc66] text-black font-bold py-2 px-6 rounded-lg hover:bg-[#00a352] transition">
                                JUEGA AQUÍ
                            </button>
                        </div>
                    </div>
                    <div class="absolute right-0 top-0 h-full w-1/3  opacity-70"></div>
                </div>

                <h3 class="text-xl font-semibold text-white mb-4">Más jugados</h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                    <a href="#" class="group block rounded-lg overflow-hidden shadow-lg transition duration-300 transform hover:scale-[1.02]">
                        <img src="/images/slots/shining.png" alt="Shining Crown" class="w-full h-auto object-cover" />
                        <div class="absolute bottom-0 left-0 right-0 p-2 bg-black/50 text-center text-sm font-semibold opacity-0 group-hover:opacity-100 transition duration-300">
                         JUGAR
                        </div>
                    </a>
                    
                    <a href="#" class="group block rounded-lg overflow-hidden shadow-lg transition duration-300 transform hover:scale-[1.02]">
                        <img src="/images/slots/lucky.png" alt="Lucky Clover" class="w-full h-auto object-cover" />
                        <div class="absolute bottom-0 left-0 right-0 p-2 bg-black/50 text-center text-sm font-semibold opacity-0 group-hover:opacity-100 transition duration-300">
                         JUGAR
                        </div>Gates of Olympus
                    </a>
                    <a href="#" class="group block rounded-lg overflow-hidden shadow-lg transition duration-300 transform hover:scale-[1.02]">
                        <img src="/images/slots/olimpo.png" alt="Gates of Olympus" class="w-full h-auto object-cover" />
                        <div class="absolute bottom-0 left-0 right-0 p-2 bg-black/50 text-center text-sm font-semibold opacity-0 group-hover:opacity-100 transition duration-300">
                         JUGAR
                        </div>
                    </a>
                    <a href="#" class="group block rounded-lg overflow-hidden shadow-lg transition duration-300 transform hover:scale-[1.02]">
                        <img src="https://via.placeholder.com/300x400/c75000/ffffff?text=Bull+Blitz+Fortunes" alt="Bull Blitz Fortunes" class="w-full h-auto object-cover" />
                    </a>
                    <a href="#" class="group block rounded-lg overflow-hidden shadow-lg transition duration-300 transform hover:scale-[1.02]">
                        <img src="https://via.placeholder.com/300x400/ff69b4/ffffff?text=Sweet+Rush+Bonanza" alt="Sweet Rush Bonanza" class="w-full h-auto object-cover" />
                    </a>
                    <a href="#" class="group block rounded-lg overflow-hidden shadow-lg transition duration-300 transform hover:scale-[1.02]">
                        <img src="https://via.placeholder.com/300x400/505050/ffffff?text=40+Shining+Crown" alt="40 Shining Crown" class="w-full h-auto object-cover" />
                    </a>
                    <a href="#" class="group block rounded-lg overflow-hidden shadow-lg transition duration-300 transform hover:scale-[1.02]">
                        <img src="https://via.placeholder.com/300x400/0000ff/ffffff?text=Hyper+Cyber+Buy" alt="Hyper Cyber Buy" class="w-full h-auto object-cover" />
                    </a>
                    <a href="#" class="group block rounded-lg overflow-hidden shadow-lg transition duration-300 transform hover:scale-[1.02]">
                        <img src="https://via.placeholder.com/300x400/222222/ffffff?text=Aphrodite" alt="Aphrodite" class="w-full h-auto object-cover" />
                    </a>
                    <a href="#" class="group block rounded-lg overflow-hidden shadow-lg transition duration-300 transform hover:scale-[1.02]">
                        <img src="https://via.placeholder.com/300x400/800080/ffffff?text=Super+7+3x3" alt="Super 7 3x3" class="w-full h-auto object-cover" />
                    </a>
                    <a href="#" class="group block rounded-lg overflow-hidden shadow-lg transition duration-300 transform hover:scale-[1.02]">
                        <img src="https://via.placeholder.com/300x400/000000/ffffff?text=The+Phantom" alt="The Phantom" class="w-full h-auto object-cover" />
                    </a>
                    </div>
            </main>
        </div>
    </div>
</template>