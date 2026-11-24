import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/website.css',
                'resources/css/website/pages/about.css',
                'resources/css/website/pages/how-it-works.css',
                'resources/css/website/pages/home.css',
                'resources/css/public-forms.css',
                'resources/js/public-forms.js',
                'resources/js/website.js',
            ],
            buildDirectory: 'build-website',
            hotFile: 'public/website.hot',
            refresh: ['Modules/Website/src/Resources/views/**/*.blade.php'],
        }),
        tailwindcss(),
    ],
});
