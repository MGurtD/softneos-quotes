import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/scss/app.scss', 'resources/js/app.js'],
            refresh: true,
            // Downloaded at build time and served from our own domain:
            // the visitor's browser never calls a third-party font CDN.
            fonts: [
                bunny('Poppins', {
                    weights: [400, 500, 600, 700],
                }),
            ],
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // Lets app.scss write @import 'tokens' for ../design/_tokens.scss.
                loadPaths: [fileURLToPath(new URL('../design', import.meta.url))],
                // Bootstrap 5.3 still uses @import and global Sass functions.
                quietDeps: true,
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
