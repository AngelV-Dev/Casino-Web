<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';

// ==========================================
// 1. DATA DEL USUARIO (Inertia)
// ==========================================
const page = usePage();
const user = page.props.auth.user;

const props = defineProps({
    balance: Number,
});

// ==========================================
// 2. LÓGICA DE LA BILLETERA
// ==========================================
const balance = ref(Number(props.balance || 0));
const casinoBalance = ref(0.00);
const bonusBalance = ref(0.00);
const depositAmount = ref('');
const withdrawAmount = ref('');

const paymentMethods = [
  { name: 'PAGOEFECTIVO QR', min: 20, max: 500, image: 'PagoEfectivoQR.png' },
  { name: 'YAPE / PLIN', min: 10, max: 500, image: 'Yape.png' },
  { name: 'VISA / MASTERCARD', min: 5, max: 20000, image: 'VisaMasterCard.png' }
];

// --- VARIABLES DEL MODAL ---
const showRechargeModal = ref(false);
const selectedMethod = ref(null);
const termsAccepted = ref(false); // Para el checkbox
const activePreset = ref(null);   // Para saber qué botón de monto está activo

// Lógica del Timer
const promotionEndDate = new Date();
promotionEndDate.setDate(promotionEndDate.getDate() + 2); // 2 días desde ahora
promotionEndDate.setHours(promotionEndDate.getHours() + 23); // 23 horas
promotionEndDate.setMinutes(promotionEndDate.getMinutes() + 51); // 51 minutos
promotionEndDate.setSeconds(promotionEndDate.getSeconds() + 9); // 9 segundos

const timer = ref({ days: '00', hours: '00', minutes: '00', seconds: '00' });
let timerInterval = null;

const updateTimer = () => {
    const now = new Date().getTime();
    const distance = promotionEndDate.getTime() - now;

    if (distance < 0) {
        clearInterval(timerInterval);
        timer.value = { days: '00', hours: '00', minutes: '00', seconds: '00' };
        return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    timer.value = {
        days: String(days).padStart(2, '0'),
        hours: String(hours).padStart(2, '0'),
        minutes: String(minutes).padStart(2, '0'),
        seconds: String(seconds).padStart(2, '0'),
    };
};

// --- Acción: Abrir Modal ---
const openRechargeModal = (method) => {
    selectedMethod.value = method;
    showRechargeModal.value = true;
    depositAmount.value = ''; // Resetear monto
    activePreset.value = null;
    termsAccepted.value = false;

    // Iniciar o reiniciar el timer al abrir el modal (si es PagoEfectivo)
    if (method.name === 'PAGOEFECTIVO QR') {
        if (timerInterval) clearInterval(timerInterval);
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    }
};

const closeRechargeModal = () => {
    showRechargeModal.value = false;
    selectedMethod.value = null;
    if (timerInterval) { // Detener el timer al cerrar el modal
        clearInterval(timerInterval);
        timerInterval = null;
    }
};

// --- Acción: Seleccionar Monto Rápido ---
const selectAmount = (amount) => {
    depositAmount.value = amount;
    activePreset.value = amount;
};

// --- Acción: Depositar ---
const deposit = async () => {
    if (!depositAmount.value || depositAmount.value <= 0) return alert("⚠️ Monto inválido.");
    
    // Validación específica para PagoEfectivo (checkbox)
    if (selectedMethod.value?.name === 'PAGOEFECTIVO QR' && !termsAccepted.value) {
        return alert("⚠️ Debes aceptar los términos y condiciones de la promo.");
    }

    // Definir la descripción para la base de datos
    let description = 'Recarga de saldo'; // Valor por defecto
    if (selectedMethod.value?.name === 'PAGOEFECTIVO QR') {
        description = 'deposito pagoefectivo qr';
    } else if (selectedMethod.value) {
        description = `Depósito vía ${selectedMethod.value.name}`;
    }

    try {
        // Enviamos 'description' junto con 'amount'
        const res = await axios.post('/wallet/deposit', { 
            amount: depositAmount.value,
            description: description 
        });

        if (res.data.success) {
            balance.value = Number(res.data.balance);
            depositAmount.value = '';
            closeRechargeModal(); // Cerrar modal al finalizar
            alert("✅ ¡Depósito exitoso!");
        } else {
            alert("❌ " + res.data.message);
        }
    } catch (e) {
        console.error(e); alert("Error al procesar.");
    }
};

const withdraw = async () => {
    // (Lógica de retiro igual que antes...)
    if (!withdrawAmount.value || withdrawAmount.value <= 0) return alert("⚠️ Monto inválido.");
    if (withdrawAmount.value > balance.value) return alert("⚠️ Fondos insuficientes.");
    try {
        const res = await axios.post('/wallet/withdraw', { amount: withdrawAmount.value });
        if (res.data.success) {
            balance.value = Number(res.data.balance);
            withdrawAmount.value = '';
            alert("✅ Retiro procesado.");
        } else {
            alert("❌ " + res.data.message);
        }
    } catch (e) {
        alert("Error de conexión.");
    }
};

// ==========================================
// 3. LÓGICA DE NAVBAR Y SIDEBAR
// ==========================================
const isCollapsed = ref(false);
const isMobileMenuOpen = ref(false);
const isMobile = ref(false);
const menuOpen = ref(false); 

const toggleSidebar = () => {
    if (isMobile.value) isMobileMenuOpen.value = !isMobileMenuOpen.value;
    else isCollapsed.value = !isCollapsed.value;
};

const closeMobileMenu = () => {
    if (isMobile.value) isMobileMenuOpen.value = false;
};

const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 768;
    if (isMobile.value) {
        isCollapsed.value = false;
        isMobileMenuOpen.value = false;
    }
};

const logout = () => {
    router.post('/logout');
};

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
    if (timerInterval) clearInterval(timerInterval); // Asegurar que el timer se detenga al salir del componente
});
</script>

<style src="../../css/oficial.css"></style>

<template>
    <Head title="Billetera" />

    <div class="flex h-screen bg-[#070a06] text-white overflow-hidden font-sans">

        <div v-if="isMobile && isMobileMenuOpen" class="mobile-overlay" @click="closeMobileMenu"></div>

        <nav class="sidebar" :class="{ collapsed: isCollapsed && !isMobile, 'mobile-open': isMobileMenuOpen && isMobile, 'mobile-closed': !isMobileMenuOpen && isMobile }">
            <div class="sidebar-header">
                <h4 class="logo-text">Logo</h4>
                <button class="toggle-btn-desktop" @click="toggleSidebar">
                    <i class="fas" :class="isCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
                </button>
            </div>
            <div class="nav-menu">
                <Link href="/" class="nav-link" @click="closeMobileMenu"><i class="fas fa-home"></i><span class="nav-text">Home</span></Link>
                <a href="#" class="nav-link" @click="closeMobileMenu"><i class="fa-solid fa-dice"></i><span class="nav-text">Slots</span></a>
                <a href="#" class="nav-link" @click="closeMobileMenu"><i class="fa-solid fa-rocket"></i><span class="nav-text">Crash</span></a>
                <Link href="/wallet" class="nav-link active" @click="closeMobileMenu"><i class="fa-solid fa-sack-dollar"></i><span class="nav-text">Billetera</span></Link>
                <a href="#" class="nav-link" @click="closeMobileMenu"><i class="fa-solid fa-bullseye"></i><span class="nav-text">Ruleta</span></a>
                <a href="#" class="nav-link" @click="closeMobileMenu"><i class="fas fa-users"></i><span class="nav-text">Comunidad</span></a>
                <a href="#" class="nav-link" @click="closeMobileMenu"><i class="fas fa-cog"></i><span class="nav-text">Settings</span></a>
            </div>
        </nav>

        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <button class="hamburger-btn" @click="toggleSidebar"><i class="fas fa-bars"></i></button>
                    <button class="back-btn"><i class="fas fa-chevron-left"></i></button>
                </div>
                <div class="header-center">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" placeholder="Buscar..." class="search-input" />
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="bg-white/10 border border-gray-600 px-4 py-2 rounded-xl shadow flex flex-col text-center min-w-[100px]">
                        <span class="text-[10px] text-gray-300 uppercase font-bold tracking-wider">Saldo</span>
                        <span class="text-sm font-mono text-lime-400 font-bold">S/ {{ balance.toFixed(2) }}</span>
                    </div>
                    <div class="relative">
                        <button @click="menuOpen = !menuOpen" class="w-10 h-10 rounded-full overflow-hidden shadow border border-gray-600">
                            <img :src="`/avatars/${user?.avatar || 'avatar_default.png'}`" alt="avatar" class="w-full h-full object-cover" />
                        </button>
                        <transition name="fade">
                            <div v-if="menuOpen" class="absolute right-0 mt-3 bg-white shadow-2xl rounded-xl w-64 p-3 z-50 border text-gray-800">
                                <Link href="/profile" class="menu-item flex items-center gap-2 p-2 hover:bg-gray-100 rounded"><i class="fas fa-user text-gray-600"></i><span>Mi perfil</span></Link>
                                <div class="menu-item flex items-center gap-2 p-2 hover:bg-gray-100 rounded cursor-pointer"><i class="fas fa-trophy text-gray-600"></i><span>Mis apuestas</span></div>
                                <button @click="logout" class="w-full mt-2 text-red-600 font-bold flex items-center gap-2 p-2 hover:bg-red-50 rounded"><i class="fas fa-power-off"></i>Cerrar sesión</button>
                            </div>
                        </transition>
                    </div>
                </div>
            </header>

            <div class="content-area p-6 overflow-y-auto h-[calc(100vh-80px)]">
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-lime-500/10 blur-[120px] rounded-full pointer-events-none"></div>
                <div class="w-full max-w-6xl mx-auto space-y-8 relative z-10">
                    
                    <div class="relative bg-gradient-to-br from-gray-800 to-gray-900 p-8 md:p-10 rounded-3xl border border-gray-700 shadow-2xl text-center overflow-hidden group">
                        <div class="absolute inset-0 bg-lime-500/5 opacity-0 group-hover:opacity-100 transition duration-700"></div>
                        <h4 class="text-gray-400 uppercase tracking-widest text-xs font-bold mb-2">Saldo Total Disponible</h4>
                        <h2 class="text-6xl md:text-7xl font-black text-white tracking-tight mb-8 drop-shadow-lg"><span class="text-lime-500 text-4xl md:text-5xl align-top mr-1">S/</span>{{ balance.toFixed(2) }}</h2>
                        <div class="grid grid-cols-2 divide-x divide-gray-700 border-t border-gray-700 pt-6 max-w-2xl mx-auto">
                            <div class="px-2"> <p class="text-xs text-gray-500 mb-1 font-bold tracking-wide">CASINO</p> <p class="text-xl font-bold text-white">S/ {{ casinoBalance.toFixed(2) }}</p> </div>
                            <div class="px-2"> <p class="text-xs text-gray-500 mb-1 font-bold tracking-wide">BONOS</p> <p class="text-xl font-bold text-amber-400">S/ {{ bonusBalance.toFixed(2) }}</p> </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-800 p-8 rounded-3xl border border-gray-700 shadow-xl relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-8 opacity-10 pointer-events-none"> <span class="text-9xl font-black text-lime-500">↓</span> </div>
                            <div class="flex items-center gap-3 mb-6 relative z-10">
                                <div class="w-12 h-12 rounded-2xl bg-lime-500/20 flex items-center justify-center text-lime-400 text-2xl font-bold">↓</div>
                                <div> <h4 class="text-2xl font-bold text-white">Depositar</h4> <p class="text-xs text-gray-400">Recarga tu cuenta</p> </div>
                            </div>
                            <div class="space-y-5 relative z-10">
                                <div class="relative"> <span class="absolute left-4 top-4 text-gray-500 font-bold">S/</span> <input v-model="depositAmount" type="number" class="w-full pl-10 pr-4 py-4 rounded-xl bg-gray-900 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:border-lime-500 focus:ring-1 focus:ring-lime-500 transition text-lg font-bold" placeholder="0.00"> </div>
                                <button @click="deposit" class="w-full py-4 rounded-xl bg-lime-500 hover:bg-lime-400 text-black font-black text-lg shadow-lg shadow-lime-500/20 active:scale-95 transition transform duration-200">RECARGAR AHORA</button>
                            </div>
                        </div>
                        <div class="bg-gray-800 p-8 rounded-3xl border border-gray-700 shadow-xl relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-8 opacity-10 pointer-events-none"> <span class="text-9xl font-black text-red-500">↑</span> </div>
                            <div class="flex items-center gap-3 mb-6 relative z-10">
                                <div class="w-12 h-12 rounded-2xl bg-red-500/20 flex items-center justify-center text-red-400 text-2xl font-bold">↑</div>
                                <div> <h4 class="text-2xl font-bold text-white">Retirar</h4> <p class="text-xs text-gray-400">Transfiere ganancias</p> </div>
                            </div>
                            <div class="space-y-5 relative z-10">
                                <div class="relative"> <span class="absolute left-4 top-4 text-gray-500 font-bold">S/</span> <input v-model="withdrawAmount" type="number" class="w-full pl-10 pr-4 py-4 rounded-xl bg-gray-900 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition text-lg font-bold" placeholder="0.00"> </div>
                                <button @click="withdraw" class="w-full py-4 rounded-xl bg-gray-700 hover:bg-red-600 hover:text-white text-gray-300 border border-gray-600 hover:border-red-500 font-black text-lg active:scale-95 transition transform duration-200">SOLICITAR RETIRO</button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 pb-12">
                        <h4 class="text-2xl font-bold text-white flex items-center gap-3"> <span class="w-2 h-8 rounded-full bg-lime-500 block"></span> Métodos de Pago </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div v-for="method in paymentMethods" :key="method.name" class="group relative bg-gray-800 rounded-3xl border border-gray-700 hover:border-lime-500 transition-all duration-300 cursor-pointer overflow-hidden shadow-2xl hover:shadow-lime-500/10 hover:-translate-y-2 flex flex-col h-full">
                                <div class="h-40 w-full bg-white flex items-center justify-center p-8 border-b border-gray-700 relative">
                                     <div class="absolute inset-0 opacity-5 bg-[radial-gradient(#000_1px,transparent_1px)] [background-size:16px_16px]"></div>
                                     <img :src="`/img_wallet/${method.image}`" :alt="method.name" class="h-full w-full object-contain drop-shadow-md group-hover:scale-110 transition duration-500" />
                                </div>
                                <div class="p-6 text-center bg-gray-800 flex-1 flex flex-col justify-between">
                                    <div>
                                        <h5 class="text-xl font-bold text-white group-hover:text-lime-400 transition mb-3">{{ method.name }}</h5>
                                        <div class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-gray-900 border border-gray-600 text-sm text-gray-400 gap-3 group-hover:border-lime-500/50 transition mb-4">
                                            <span class="font-mono">Min: {{ method.min }}</span> <span class="text-gray-600">|</span> <span class="font-mono">Max: {{ method.max }}</span>
                                        </div>
                                    </div>
                                    <button @click="openRechargeModal(method)" class="w-full py-3 rounded-xl bg-lime-500 hover:bg-lime-400 text-black font-bold shadow-lg shadow-lime-500/20 active:scale-95 transition transform duration-200 flex items-center justify-center gap-2">
                                        <span>Recargar</span> <i class="fas fa-arrow-right text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <transition name="fade">
            <div v-if="showRechargeModal && selectedMethod?.name === 'PAGOEFECTIVO QR'" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" @click="closeRechargeModal"></div>

                <div class="relative bg-gray-900 w-full max-w-xl rounded-3xl border border-gray-700 shadow-2xl overflow-hidden transform transition-all scale-100">
                    
                    <div class="relative p-6 text-center border-b border-gray-800">
                        <button @click="closeRechargeModal" class="absolute left-6 top-6 text-gray-400 hover:text-white transition">
                            <i class="fas fa-chevron-left text-xl"></i>
                        </button>
                        <h3 class="text-xl font-bold text-white">Recarga</h3>
                        <button @click="closeRechargeModal" class="absolute right-6 top-6 text-gray-400 hover:text-white transition">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="p-6 md:p-8 overflow-y-auto max-h-[80vh]">
                        
                        <div class="flex flex-col items-center mb-8">
                            <div class="mb-2 text-xs text-gray-400 font-semibold uppercase tracking-wide">Código QR vía</div>
                            <div class="bg-[#FFCC00] text-black px-6 py-2 rounded-full font-black text-xl flex items-center gap-2 mb-3 shadow-lg shadow-yellow-500/20">
                                <span class="text-2xl">❖</span> PagoEfectivo
                            </div>
                            <div class="flex gap-3">
                                <div class="bg-white w-8 h-8 rounded flex items-center justify-center"><i class="fas fa-qrcode text-black text-sm"></i></div>
                                <div class="bg-purple-600 w-8 h-8 rounded flex items-center justify-center text-white font-bold text-[10px]">Yape</div>
                                <div class="bg-blue-400 w-8 h-8 rounded flex items-center justify-center text-white font-bold text-[10px]">plin</div>
                                <div class="bg-blue-800 w-8 h-8 rounded flex items-center justify-center text-white font-bold text-[10px]">BBVA</div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-400 mb-3">Monto a depositar</label>
                            <div class="grid grid-cols-4 gap-3">
                                <button @click="selectAmount(50)" :class="activePreset === 50 ? 'bg-[#FFCC00] text-black border-[#FFCC00]' : 'bg-transparent text-gray-400 border-gray-600 hover:border-white'" class="border rounded-lg py-2 font-bold transition text-sm">S/ 50</button>
                                
                                <div class="relative">
                                    <div class="absolute -top-2 -right-2 bg-[#FFCC00] text-black text-[10px] w-5 h-5 flex items-center justify-center rounded-full shadow-sm z-10"><i class="fas fa-gift"></i></div>
                                    <button @click="selectAmount(100)" :class="activePreset === 100 ? 'bg-[#FFCC00] text-black border-[#FFCC00]' : 'bg-transparent text-gray-400 border-gray-600 hover:border-white'" class="w-full border rounded-lg py-2 font-bold transition text-sm">S/ 100</button>
                                </div>

                                <div class="relative">
                                    <div class="absolute -top-2 -right-2 bg-[#FFCC00] text-black text-[10px] w-5 h-5 flex items-center justify-center rounded-full shadow-sm z-10"><i class="fas fa-gift"></i></div>
                                    <button @click="selectAmount(300)" :class="activePreset === 300 ? 'bg-[#FFCC00] text-black border-[#FFCC00]' : 'bg-transparent text-gray-400 border-gray-600 hover:border-white'" class="w-full border rounded-lg py-2 font-bold transition text-sm">S/ 300</button>
                                </div>

                                <div class="relative">
                                    <div class="absolute -top-2 -right-2 bg-[#FFCC00] text-black text-[10px] w-5 h-5 flex items-center justify-center rounded-full shadow-sm z-10"><i class="fas fa-gift"></i></div>
                                    <button @click="selectAmount(500)" :class="activePreset === 500 ? 'bg-[#FFCC00] text-black border-[#FFCC00]' : 'bg-transparent text-gray-400 border-gray-600 hover:border-white'" class="w-full border rounded-lg py-2 font-bold transition text-sm">S/ 500</button>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#FFF8E1]/10 rounded-xl p-4 flex gap-3 mb-6 border border-[#FFE58F]/30">
                            <div class="text-2xl text-yellow-400">🎁</div>
                            <p class="text-xs text-gray-200 leading-relaxed">
                                <strong>¡Del martes 25 de noviembre al jueves 27 de noviembre</strong>, tu recarga de S/100 con PagoEfectivo QR viene con una apuesta deportiva gratis de S/10.00!
                            </p>
                        </div>

                        <div class="flex items-center gap-3 mb-6">
                            <input type="checkbox" id="terms" v-model="termsAccepted" class="w-5 h-5 rounded border-gray-500 bg-transparent text-[#FFCC00] focus:ring-0 focus:ring-offset-0 cursor-pointer">
                            <label for="terms" class="text-xs text-gray-400 cursor-pointer select-none">
                                He leído y acepto los <span class="text-white font-bold underline">Términos y condiciones de la promo</span>
                            </label>
                        </div>

                        <div class="flex rounded-xl overflow-hidden border border-[#FFCC00] h-14 mb-4 shadow-lg shadow-yellow-500/10">
                            <div class="bg-white flex items-center px-4 text-black font-bold text-lg">S/</div>
                            <input v-model="depositAmount" type="number" class="flex-1 bg-white text-black text-2xl font-bold focus:outline-none px-2" placeholder="100">
                            <button @click="deposit" class="bg-[#FFCC00] text-black font-bold px-6 hover:bg-[#ffdb4d] transition uppercase text-sm tracking-wide">
                                Recargar
                            </button>
                        </div>

                        <p class="text-center text-[10px] text-gray-500 mb-8">
                            Mínimo S/ {{ selectedMethod?.min || '20.00' }} y máximo S/ {{ selectedMethod?.max || '500.00' }}*
                        </p>

                        <div class="text-center mb-8">
                            <p class="text-sm font-bold text-white mb-3">La promo termina en:</p>
                            <div class="flex justify-center gap-2 text-black">
                                <div class="bg-gray-200 rounded-lg w-12 py-2">
                                    <div class="text-xl font-bold leading-none">{{ timer.days }}</div>
                                    <div class="text-[9px] text-gray-600 uppercase">Días</div>
                                </div>
                                <div class="self-center text-white font-bold">:</div>
                                <div class="bg-gray-200 rounded-lg w-12 py-2">
                                    <div class="text-xl font-bold leading-none">{{ timer.hours }}</div>
                                    <div class="text-[9px] text-gray-600 uppercase">Horas</div>
                                </div>
                                <div class="self-center text-white font-bold">:</div>
                                <div class="bg-gray-200 rounded-lg w-12 py-2">
                                    <div class="text-xl font-bold leading-none">{{ timer.minutes }}</div>
                                    <div class="text-[9px] text-gray-600 uppercase">Min.</div>
                                </div>
                                <div class="self-center text-white font-bold">:</div>
                                <div class="bg-gray-200 rounded-lg w-12 py-2">
                                    <div class="text-xl font-bold leading-none">{{ timer.seconds }}</div>
                                    <div class="text-[9px] text-gray-600 uppercase">Seg.</div>
                                </div>
                            </div>
                        </div>

                        <button @click="closeRechargeModal" class="w-full text-gray-400 hover:text-white text-sm flex items-center justify-center gap-2 transition">
                            <i class="fas fa-arrow-left"></i> Elegir otro medio de pago
                        </button>

                    </div>
                </div>
            </div>
        </transition>

    </div>
</template>