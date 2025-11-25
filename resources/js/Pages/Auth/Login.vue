<script setup>
/**
 * ==========================================
 * LÓGICA DEL SCRIPT (Vue 3 + Inertia)
 * ==========================================
 */
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/components/PrimaryButton.vue';
// Nota: Asegúrate de que la carpeta coincida con tu sistema (Components vs components)
import Checkbox from '@/components/Checkbox.vue'; 

// Definimos si recibimos propiedades externas (como status de sesión o permisos de resetear)
defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

// useForm: Crea el objeto reactivo para manejar los datos del login
const form = useForm({
    email: '',
    password: '',
    remember: false, // Checkbox de "Recordarme"
});

// Función de envío del formulario
const submit = () => {
    form.post(route('login'), {
        // Al terminar, limpiamos el campo password por seguridad para que no quede escrito
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <!-- 
      ==========================================
                 CONTENEDOR PRINCIPAL
      ==========================================
      w-full            : Ancho total.
      min-h-screen      : Altura completa de la ventana (evita bordes blancos en móvil).
      bg-[#070a06]      : FONDO NEGRO PROFUNDO (Personalizado).
      flex items-center : Centrado vertical.
      justify-center    : Centrado horizontal.
      px-4 py-8         : Espaciado interno seguro.
      relative          : Para posicionar elementos absolutos si fuera necesario.
    -->
    <div class="w-full min-h-screen flex flex-col items-center justify-center bg-[#070a06] px-4 py-8 relative">

        <!-- 
          ==========================================
                 TARJETA DE CONTENIDO
          ==========================================
          max-w-lg   : Limita el ancho en pantallas grandes para que no se vea estirado.
          w-full     : En móvil usa todo el espacio.
          text-white : Texto blanco base.
          z-10       : Capa superior.
        -->
        <div class="w-full max-w-lg flex flex-col justify-center text-white z-10">
            
            <!-- HEADER / LOGO -->
            <div class="mb-10 text-center">
                <h2 class="text-2xl font-bold tracking-widest uppercase">
                    JACKPOT <span class="text-neon-green">CELESTIAL</span>
                </h2>
                <!-- Barra decorativa inferior -->
                <div class="h-0.5 w-24 bg-gray-600 mt-2 mx-auto"></div>
            </div>

            <!-- TÍTULOS DE BIENVENIDA -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-neon-green">Bienvenido Nuevamente!</h1>
                <p class="mt-2 text-lg text-gray-400">Porfavor, inicia sesión en tu cuenta.</p>
            </div>

            <!-- Muestra mensaje de estado si existe (ej: "Contraseña restablecida") -->
            <div v-if="status" class="mb-4 font-medium text-sm text-green-600 text-center">
                {{ status }}
            </div>

            <!-- FORMULARIO -->
            <form @submit.prevent="submit" class="space-y-5">

                <!-- 
                  ==========================================
                             INPUT 1: EMAIL
                  ==========================================
                -->
                <div>
                    <div class="relative">
                        <!-- ICONO (Posicionado absolutamente a la izquierda) -->
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <!-- 
                           Input:
                           bg-transparent   : Fondo transparente.
                           rounded-full     : BORDES REDONDOS (Estilo Píldora).
                           focus:ring-neon-green : Anillo de luz verde al enfocar.
                        -->
                        <input
                            type="email"
                            v-model="form.email"
                            placeholder="Correo Electronico"
                            class="w-full pl-12 pr-4 py-3 bg-transparent border border-gray-600 rounded-full text-white placeholder-gray-500 focus:outline-none focus:border-neon-green focus:ring-1 focus:ring-neon-green transition-all"
                            required
                            autofocus
                        />
                    </div>
                    <div class="text-red-500 text-xs mt-1 ml-4">{{ form.errors.email }}</div>
                </div>

                <!-- 
                  ==========================================
                             INPUT 2: PASSWORD
                  ==========================================
                -->
                <div>
                    <div class="relative">
                        <!-- ICONO (Candado) -->
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input
                            type="password"
                            v-model="form.password"
                            placeholder="Contraseña"
                            class="w-full pl-12 pr-4 py-3 bg-transparent border border-gray-600 rounded-full text-white placeholder-gray-500 focus:outline-none focus:border-neon-green focus:ring-1 focus:ring-neon-green transition-all"
                            required
                            autocomplete="current-password"
                        />
                    </div>
                    <div class="text-red-500 text-xs mt-1 ml-4">{{ form.errors.password }}</div>
                </div>

                <!-- 
                  ==========================================
                       OPCIONES: REMEMBER & FORGOT
                  ==========================================
                  flex justify-between : Pone uno a la izquierda y otro a la derecha.
                -->
                <div class="flex items-center justify-between text-sm px-2">
                    
                    <!-- CHECKBOX "REMEMBER ME" -->
                    <label class="flex items-center cursor-pointer">
                        <Checkbox 
                            name="remember" 
                            v-model:checked="form.remember" 
                            class="text-neon-green bg-transparent border-gray-600 focus:ring-neon-green rounded" 
                        />
                        <span class="ml-2 text-gray-400 select-none hover:text-white transition">Recuerdame</span>
                    </label>

                    <!-- ENLACE "FORGOT PASSWORD" -->
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-gray-400 hover:text-neon-green transition-colors"
                    >
                        Olvidaste tu contraseña?
                    </Link>
                </div>

                <!-- 
                  ==========================================
                             BOTÓN DE LOGIN
                  ==========================================
                  bg-gradient-to-r : Degradado Verde Neón -> Verde Oscuro.
                  rounded-full     : Botón Redondo.
                  shadow-neon-green/40 : Efecto de resplandor (Glow).
                -->
                <PrimaryButton
                    class="w-full justify-center py-3 rounded-full mt-6 bg-gradient-to-r from-[#00FF00] to-[#008000] text-black font-bold hover:opacity-90 shadow-lg shadow-neon-green/40 border-none"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Ingresar
                </PrimaryButton>

                <!-- ENLACE AL REGISTRO -->
                <div class="text-sm mt-4 text-center text-gray-400">
                    No tienes una cuenta?
                    <Link
                        :href="route('register')"
                        class="underline text-neon-green hover:text-green-300 font-bold ml-1"
                    >
                        Crear Cuenta
                    </Link>
                </div>

            </form>
        </div>
    </div>
</template>
<!-- 
==========================================
                   __
                  / _)
         _/\/\/\_/ /
       _|         /
     _|   ( |  ( |
    /__.-'|_|--|_| Tovar
 ==========================================
-->