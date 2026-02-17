import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                serif: ['Playfair Display', ...defaultTheme.fontFamily.serif],
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'primary': '#8B4513',
                'primary-light': '#A0522D',
                'primary-dark': '#654321',
                'gold': '#D4AF37',
                'gold-light': '#F4D03F',
                'cream': '#FAF8F5',
                'cream-dark': '#F5F0EB',
                'warm-gray': '#9A8B7A',
                'dark': '#2C2416',
            },
        },
    },

    plugins: [forms],
};
