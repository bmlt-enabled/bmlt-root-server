import laravel from 'laravel-vite-plugin'
import { svelte } from "@sveltejs/vite-plugin-svelte";
import type { UserConfig } from 'vite';

const config: UserConfig = {
    build: {
        minify: false,
    },

    plugins: [
        laravel([
            'resources/css/app.scss',
            'resources/js/main.ts'
        ]),
        svelte()
    ]
};

export default config;
