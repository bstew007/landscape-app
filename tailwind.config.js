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
                brand: {
                    50: '#fbfbfc',
                    100: '#f1f2f5',
                    200: '#e1e3ea',
                    300: '#c8cbd7',
                    400: '#a4a8ba',
                    500: '#81889d',
                    600: '#6b7285',
                    700: '#555a6a',
                    800: '#3f4350',
                    900: '#2d2f38',
                },
                accent: {
                    50: '#edf7f1',
                    100: '#cfe9db',
                    200: '#add7c2',
                    300: '#84c2a3',
                    400: '#55a97e',
                    500: '#2f8f5f',
                    600: '#236f47',
                    700: '#1c5638',
                    800: '#153c28',
                    900: '#0d271a',
                },
            },
        },
    },

    plugins: [forms],
};
