import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                // Colores personalizados para el diseño del Casino
                'dark-bg': '#0f100fff',          // Fondo general, muy oscuro
                'input-bg': '#a6bba1ff',         // Fondo de los inputs
                'neon-green': '#00FF00',       // Verde brillante (inicio del gradiente y texto "Hello Again!")
                'darker-green': '#008000',     // Verde oscuro (fin del gradiente del botón)
                'placeholder-gray': '#AAAAAA', // Gris para placeholders y texto secundario
                'link-gray': '#555555',        // Gris para enlaces como "Forgot Password"
                'vertical-text-gray': '#333333', // Gris para el texto vertical "Sign in"
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};