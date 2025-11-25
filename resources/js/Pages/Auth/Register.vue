<script setup>
/**
 * ==========================================
 * LÓGICA DEL SCRIPT
 * ==========================================
 */
import { Head, Link, useForm } from '@inertiajs/vue3'; // Importamos herramientas de Inertia para navegación y formularios
import PrimaryButton from '@/components/PrimaryButton.vue'; // Importamos nuestro componente de botón reutilizable

// Definimos el objeto 'form' que guardará los datos del usuario
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

// Función para enviar el formulario al Backend (Laravel)
const submit = () => {
    form.post(route('register'), {
        // Cuando termine (exito o error), limpiamos los campos de contraseña por seguridad
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <!-- 
      ==========================================
                 CONTENEDOR PRINCIPAL
      ==========================================
      w-full             : Ancho total de la página.
      min-h-screen       : Altura mínima del 100% de la ventana (para que no queden huecos blancos).
      flex items-center  : Centra el contenido verticalmente.
      justify-center     : Centra el contenido horizontalmente.
      bg-[#070a06]       : COLOR DE FONDO (Negro Profundo personalizado).
      px-4 py-8          : Espaciado interno para que el contenido no toque los bordes en móviles.
      relative           : Necesario para poder posicionar la firma absoluta en la parte inferior si quisieras.
    -->
    <div class="w-full min-h-screen flex flex-col items-center justify-center bg-[#070a06] px-4 py-8 relative">

        <!-- 
          ==========================================
                 CAJA DEL CONTENIDO (Card)
          ==========================================
          w-full     : Ocupa todo el ancho disponible en móviles.
          max-w-lg   : En pantallas grandes, se limita a un tamaño "Large" (aprox 500px) para elegancia.
          flex-col   : Organiza los elementos (Logo, Título, Form) uno debajo del otro.
          text-white : Todo el texto dentro será blanco por defecto.
        -->
        <div class="w-full max-w-lg flex flex-col justify-center text-white z-10">
            
            <!-- LOGO / HEADER -->
            <div class="mb-10 text-center">
                <!-- 
                   tracking-widest : Separa mucho las letras para un look cinematográfico/premium.
                   uppercase       : Fuerza todo el texto a mayúsculas.
                -->
                <h2 class="text-2xl font-bold tracking-widest uppercase">
                    JACKPOT <span class="text-neon-green">CELESTIAL</span>
                </h2>
                <!-- Línea decorativa debajo del logo -->
                <div class="h-0.5 w-24 bg-gray-600 mt-2 mx-auto"></div>
            </div>

            <!-- SALUDO / BIENVENIDA -->
            <div class="text-center mb-8">
                <!-- text-neon-green: Usa tu color personalizado verde brillante -->
                <h1 class="text-4xl font-bold text-neon-green">Hola!</h1>
                <p class="mt-2 text-lg text-gray-400">Registrate para iniciar</p>
            </div>

            <!-- FORMULARIO -->
            <form @submit.prevent="submit" class="space-y-5">

                <!-- 
                  ==========================================
                             INPUT 1: NOMBRE
                  ==========================================
                -->
                <div>
                    <div class="relative">
                        <!-- ICONO SVG (Posicionado absolutamente a la izquierda del input) -->
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <!-- 
                           ESTILOS DEL INPUT:
                           w-full             : Ancho completo.
                           pl-12              : Padding izquierdo grande para dejar espacio al icono.
                           bg-transparent     : Fondo transparente para ver el negro de atrás.
                           border-gray-600    : Borde gris sutil por defecto.
                           rounded-full       : BORDES REDONDOS (Estilo Píldora).
                           focus:border-neon-green : Al hacer clic, el borde cambia a verde neón.
                           focus:ring-neon-green   : Al hacer clic, aparece un anillo de luz verde.
                           transition-all     : Suaviza los cambios de color.
                        -->
                        <input
                            type="text"
                            v-model="form.name"
                            placeholder="Nombres"
                            class="w-full pl-12 pr-4 py-3 bg-transparent border border-gray-600 rounded-full text-white placeholder-gray-500 focus:outline-none focus:border-neon-green focus:ring-1 focus:ring-neon-green transition-all"
                            required
                        />
                    </div>
                    <!-- Mensaje de error (Rojo) si falla la validación -->
                    <div class="text-red-500 text-xs mt-1 ml-4">{{ form.errors.name }}</div>
                </div>

                <!-- 
                  ==========================================
                             INPUT 2: EMAIL
                  ==========================================
                -->
                <div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input
                            type="email"
                            v-model="form.email"
                            placeholder="Correo Electronico"
                            class="w-full pl-12 pr-4 py-3 bg-transparent border border-gray-600 rounded-full text-white placeholder-gray-500 focus:outline-none focus:border-neon-green focus:ring-1 focus:ring-neon-green transition-all"
                            required
                        />
                    </div>
                    <div class="text-red-500 text-xs mt-1 ml-4">{{ form.errors.email }}</div>
                </div>

                <!-- 
                  ==========================================
                             INPUT 3: PASSWORD
                  ==========================================
                -->
                <div>
                    <div class="relative">
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
                        />
                    </div>
                    <div class="text-red-500 text-xs mt-1 ml-4">{{ form.errors.password }}</div>
                </div>

                <!-- 
                  ==========================================
                       INPUT 4: CONFIRM PASSWORD
                  ==========================================
                -->
                <div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <input
                            type="password"
                            v-model="form.password_confirmation"
                            placeholder="Confirmar contraseña"
                            class="w-full pl-12 pr-4 py-3 bg-transparent border border-gray-600 rounded-full text-white placeholder-gray-500 focus:outline-none focus:border-neon-green focus:ring-1 focus:ring-neon-green transition-all"
                            required
                        />
                    </div>
                    <div class="text-red-500 text-xs mt-1 ml-4">{{ form.errors.password_confirmation }}</div>
                </div>

                <!-- 
                  ==========================================
                             BOTÓN DE REGISTRO
                  ==========================================
                  Explicación de clases detallada:
                  
                  w-full             : El botón ocupa todo el ancho del formulario.
                  justify-center     : El texto "Sign Up" aparece centrado.
                  py-3               : Altura del botón (padding vertical).
                  rounded-full       : IMPORTANTE -> Bordes completamente redondos (Píldora).
                  mt-6               : Margen superior para separarlo de los inputs.
                  
                  --- ESTILOS DE COLOR ---
                  bg-gradient-to-r   : Activa el modo degradado horizontal (izquierda a derecha).
                  from-[#00FF00]     : Color de inicio (Verde Neón Brillante).
                  to-[#008000]       : Color final (Verde Oscuro sólido).
                  
                  --- ESTILOS DE TEXTO ---
                  text-black         : Texto negro para máximo contraste con el verde.
                  font-bold          : Letra gruesa para que destaque.
                  
                  --- EFECTOS VISUALES ---
                  hover:opacity-90         : Al pasar el mouse, se hace un poco transparente.
                  shadow-lg                : Sombra grande para dar efecto 3D.
                  shadow-neon-green/40     : La sombra es de color verde neón (efecto resplandor/glow).
                  border-none              : Sin bordes de línea.
                -->
                <PrimaryButton
                    class="w-full justify-center py-3 rounded-full mt-6 bg-gradient-to-r from-[#00FF00] to-[#008000] text-black font-bold hover:opacity-90 shadow-lg shadow-neon-green/40 border-none"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Registrate
                </PrimaryButton>

                <!-- LINK AL LOGIN -->
                <div class="text-sm mt-4 text-center text-gray-400">
                    Ya estás registrado?
                    <Link
                        :href="route('login')"
                        class="underline text-neon-green hover:text-green-300 font-bold ml-1"
                    >
                        Inicia Sesion
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