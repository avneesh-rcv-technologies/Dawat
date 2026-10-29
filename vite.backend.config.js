import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'Modules/Backend/src/Resources/assets/css/app.css',
                'Modules/Backend/src/Resources/assets/css/toast.css',
                'Modules/KYC/src/Resources/assets/css/kyc.css',
                'Modules/Backend/src/Resources/assets/js/toast.js',
                'Modules/Backend/src/Resources/assets/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
