import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
        },
    },
    define: {
        'import.meta.env.VITE_VAPID_PUBLIC_KEY': JSON.stringify('BOSRA9-AEa_yQ6XAHCvYBA6e82S3MliX2hEIn5PGKPV55HH2E3jgkGwxiJzU58wS_ZCpGeHHlmkmxffnpj2F98U'),
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/spa/app.js'],
            refresh: true,
        }),
        vue(),
        VitePWA({
            strategies: 'injectManifest',
            srcDir: 'resources/js/spa',
            filename: 'sw.js',
            outDir: 'public',
            injectManifest: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
            },
            manifest: {
                name: 'CatatUang - Personal Finance Tracker',
                short_name: 'CatatUang',
                description: 'CatatUang - Personal Finance Tracker untuk manajemen keuangan multi-wallet Anda',
                theme_color: '#10b981',
                background_color: '#ffffff',
                display: 'standalone',
                orientation: 'portrait',
                scope: '/',
                start_url: '/spa',
                icons: [
                    {
                        src: '/icons/icon-192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/icons/icon-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                    {
                        src: '/icons/icon-512-maskable.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable',
                    },
                ],
            },
        }),
    ],
});
