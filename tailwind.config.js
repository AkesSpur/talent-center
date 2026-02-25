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

    safelist: [
        // ContestStatus badge colors (generated from PHP enum, not scanned by JIT)
        'bg-gray-100', 'text-gray-700',
        'bg-blue-100', 'text-blue-700',
        'bg-green-100', 'text-green-700',
        'bg-yellow-100', 'text-yellow-700', 'text-yellow-800',
        'bg-orange-100', 'text-orange-700',
        'bg-red-100', 'text-red-700',
        // ApplicationStatus badge colors
        'bg-gray-200', 'text-gray-600',
    ],

    plugins: [forms],
};
