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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#2F855A',
                'primary-content': '#ffffff', // Add explicit colors to replace DaisyUI semantic colors
                neutral: '#374151',
                'neutral-content': '#ffffff',
            }
        },
    },

    plugins: [
        forms,
        // Remove require('daisyui') line
    ],
};
