import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { local } from 'laravel-vite-plugin/fonts';
import { defineConfig, lazyPlugins } from 'vite-plus';

export default defineConfig({
    plugins: lazyPlugins(() => [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/passkeys.js',
            ],
            refresh: true,
            fonts: [
                local('Instrument Serif', {
                    src: 'resources/fonts/instrument-serif/*.woff2',
                    optimizedFallbacks: false,
                }),
                local('Instrument Sans', {
                    variants: [400, 500, 600].map((weight) => ({
                        src: 'resources/fonts/instrument-sans/' + weight + '-normal.woff2',
                        weight,
                    })),
                    preload: [{ weight: 400, style: 'normal' }],
                    optimizedFallbacks: false,
                }),
            ],
        }),
        tailwindcss(),
    ]),
    server: {
        cors: true,
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/storage/framework/views/**',
                '**/vendor/**',
            ],
        },
    },
});
