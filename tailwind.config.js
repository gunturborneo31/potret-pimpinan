import defaultTheme from 'tailwindcss/defaultTheme';
import colors from 'tailwindcss/colors';
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
                orange: {
                    ...colors.orange,
                    50: '#eef3ff',
                    100: '#d9e4ff',
                    200: '#bcd0ff',
                    300: '#8faeff',
                    400: '#5d84f6',
                    500: '#3b63df',
                    600: '#274CA5',
                    700: '#1f3d84',
                    800: '#1d356c',
                    900: '#1d3059',
                    950: '#141e36',
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    darkMode: 'class',

    plugins: [forms],
};
