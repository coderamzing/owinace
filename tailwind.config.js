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
                    50:  '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#F37B7F',
                    600: '#dc2626',
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                    950: '#450a0a',
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
                    50:  '#f0f4f8',
                    100: '#d9e2ec',
                    200: '#bcccdc',
                    300: '#9fb3c8',
                    400: '#829ab1',
                    500: '#627d98',
                    600: '#486581',
                    700: '#334e68',
                    800: '#243b53',
                    900: '#272E3F',
                    950: '#102a43',
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
                'gradient-primary': 'linear-gradient(135deg, #dc2626 0%, #F37B7F 100%)',
                'gradient-secondary': 'linear-gradient(135deg, #243b53 0%, #272E3F 100%)',
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
