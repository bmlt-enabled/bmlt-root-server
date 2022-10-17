import preprocess from "svelte-preprocess";
import adapter from '@sveltejs/adapter-static';

/** @type {import('@sveltejs/kit').Config} */
const config = {
    // Consult https://github.com/sveltejs/svelte-preprocess
    // for more information about preprocessors
    preprocess: preprocess(),

    kit: {
        adapter: adapter({
            fallback: 'index.html'
        }),
        files: {
            assets: 'resources/static',
            lib: 'resources/sveltekit/lib',
            params: 'resources/sveltekit/params',
            routes: 'resources/sveltekit/routes',
            serviceWorker: 'resources/sveltekit/routes',
            appTemplate: 'resources/sveltekit/app.html',
            errorTemplate: 'resources/sveltekit/error.html'
        }
    }
};

export default config;
