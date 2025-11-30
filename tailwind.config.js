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

    // ✅ SAFELIST - Evita que Tailwind purgue estas clases
    safelist: [
        {
            pattern: /^(bg|text|border|from|to|via)-(lime|green|purple|white)-(400|500|600|900)/,
            variants: ['hover', 'focus'],
        },
        'bg-gradient-to-r',
        'bg-gradient-to-br',
        'backdrop-blur-md',
        'bg-white/10',
        'bg-white/15',
        'bg-white/20',
        'border-white/20',
        'border-white/30',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'neon-green': '#00FF00',
                'darker-green': '#00AA00',
                'dark-bg': '#070a06',
                'vertical-text-gray': '#2c2c2c',
            },
        },
    },

    plugins: [forms],
};