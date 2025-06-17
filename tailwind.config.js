import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        'animate-spin', // ⬅️ Dodaj to
        'opacity-60',   // ⬅️ Dla przycisku w trakcie ładowania
        'cursor-not-allowed',
        'w-5', 'h-5', 'ml-2', 'opacity-25', 'opacity-75'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
