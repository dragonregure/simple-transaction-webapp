import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';

const vitePort = Number(process.env.VITE_PORT ?? 5173);

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: vitePort,
        strictPort: true,
        origin: `http://localhost:${vitePort}`,
        hmr: {
            host: 'localhost',
            port: vitePort,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
