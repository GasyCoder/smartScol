import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/dashwin/css/app.css',
                'resources/dashwin/js/scripts.js',
                'resources/dashwin/js/apps.js',
                'resources/dashwin/js/charts.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5174,
        hmr: {
            host: '192.168.88.198',
            port: 5174
        }
    }
});