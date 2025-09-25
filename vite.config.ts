import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        // Plugin oficial para integrar Vite con Laravel.
        // - input: punto de entrada del frontend (Vue + Inertia).
        // - ssr: entry para renderizado del lado del servidor (si se usa Inertia SSR).
        // - refresh: recarga automática en desarrollo cuando cambian vistas/rutas.
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        // Plugin de Tailwind CSS para habilitar features durante el build/dev.
        tailwindcss(),
        // Plugin de Vue para que Vite entienda archivos .vue y resuelva assets en plantillas.
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
