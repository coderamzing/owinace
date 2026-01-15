import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import preset from './vendor/filament/support/tailwind.config.preset';

/** @type {import('tailwindcss').Config} */
export default {
    presets: [preset],

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/css/**/*.css',
        './app/Filament/**/*.php',
        './vendor/filament/**/*.blade.php',
    ],

    theme: {
        extend: {
            // fontFamily: {
            //     sans: [
            //         '"Segoe UI"',
            //         'system-ui',
            //         '-apple-system',
            //         'BlinkMacSystemFont',
            //         '"Helvetica Neue"',
            //         'Arial',
            //         ...defaultTheme.fontFamily.sans,
            //     ],
            // },
            // colors: {
            //     primary: {
            //         '50': '#dfdfdf',
            //         '100': '#e8ffdb',
            //         '200': '#d7ffb7',
            //         '300': '#c6ff93',
            //         '400': '#b5ff6f',
            //         '500': '#a4ff4b',
            //         '600': '#93ff27',
            //         '700': '#82ff03',
            //         '800': '#71ffdf',
            //         '900': '#000000',
            //     },
            // },
            // borderRadius: {
            //     DEFAULT: '0.375rem',
            //     md: '0.5rem',
            // },
            // boxShadow: {
            //     fluent: '0 1px 3px rgba(0,0,0,0.12), 0 6px 10px rgba(0,0,0,0.08)',
            //     'premium': '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
            //     'premium-lg': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
            // },
            // backgroundImage: {
            //     'gradient-primary': 'linear-gradient(135deg, #dc2626 0%, #F37B7F 100%)',
            //     'gradient-secondary': 'linear-gradient(135deg, #243b53 0%, #272E3F 100%)',
            //     'gradient-success': 'linear-gradient(135deg, #059669 0%, #10B981 100%)',
            //     'gradient-danger': 'linear-gradient(135deg, #DC2626 0%, #EF4444 100%)',
            //     'gradient-warning': 'linear-gradient(135deg, #D97706 0%, #F59E0B 100%)',
            //     'gradient-info': 'linear-gradient(135deg, #2563EB 0%, #3B82F6 100%)',
            // },
        },
    },

    plugins: [forms],
    
    // safelist: [
    //     'bg-primary-600',
    //     'bg-primary-700',
    //     'bg-primary-500',
    //     'bg-primary-50',
    //     'text-primary-600',
    //     'text-primary-700',
    //     'border-primary-600',
    //     'ring-primary-600',
    //     'ring-primary-500',
    //     'ring-primary-300',
    // ],
};
