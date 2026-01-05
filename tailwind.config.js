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
            fontFamily: {
                sans: [
                    '"Segoe UI"',
                    'system-ui',
                    '-apple-system',
                    'BlinkMacSystemFont',
                    '"Helvetica Neue"',
                    'Arial',
                    ...defaultTheme.fontFamily.sans,
                ],
            },
            colors: {
                primary: {
                    50:  '#f0fdfa',
                    100: '#ccfbf1',
                    200: '#99f6e4',
                    300: '#5eead4',
                    400: '#2dd4bf',
                    500: '#14b8a6',
                    600: '#0d9488',
                    700: '#0f766e',
                    800: '#115e59',
                    900: '#134e4a',
                    950: '#042f2e',
                },
                neutral: {
                    50:  '#f8fafc',
                    100: '#f1f5f9',
                    200: '#e2e8f0',
                    300: '#cbd5e1',
                    400: '#94a3b8',
                    500: '#64748b',
                    600: '#475569',
                    700: '#334155',
                    800: '#1e293b',
                    900: '#0f172a',
                    950: '#020617',
                },
                secondary: {
                    50:  '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#34d399',
                    500: '#10b981',
                    600: '#059669',
                    700: '#047857',
                    800: '#065f46',
                    900: '#064e3b',
                    950: '#022c22',
                },
            },
            borderRadius: {
                DEFAULT: '0.375rem',
                md: '0.5rem',
            },
            boxShadow: {
                fluent: '0 1px 3px rgba(0,0,0,0.12), 0 6px 10px rgba(0,0,0,0.08)',
                'premium': '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
                'premium-lg': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
            },
            backgroundImage: {
                'gradient-primary': 'linear-gradient(135deg, #0F766E 0%, #14B8A6 100%)',
                'gradient-secondary': 'linear-gradient(135deg, #10B981 0%, #34D399 100%)',
                'gradient-success': 'linear-gradient(135deg, #059669 0%, #10B981 100%)',
                'gradient-danger': 'linear-gradient(135deg, #DC2626 0%, #EF4444 100%)',
                'gradient-warning': 'linear-gradient(135deg, #D97706 0%, #F59E0B 100%)',
                'gradient-info': 'linear-gradient(135deg, #2563EB 0%, #3B82F6 100%)',
            },
        },
    },

    plugins: [forms],
    
    safelist: [
        'bg-primary-600',
        'bg-primary-700',
        'bg-primary-500',
        'bg-primary-50',
        'text-primary-600',
        'text-primary-700',
        'border-primary-600',
        'ring-primary-600',
        'ring-primary-500',
        'ring-primary-300',
    ],
};
