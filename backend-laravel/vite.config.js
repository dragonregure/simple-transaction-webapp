import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';

const vitePort = Number(process.env.VITE_PORT ?? 5173);
const appUrl = process.env.APP_URL ?? 'http://localhost:8000';
const viteOrigin = process.env.VITE_DEV_SERVER_URL ?? `http://localhost:${vitePort}`;

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
        origin: viteOrigin,
        cors: {
            origin: [appUrl, viteOrigin],
        },
        hmr: {
            host: 'localhost',
            port: vitePort,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
