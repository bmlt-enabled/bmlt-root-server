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
            lib: 'resources/kit/lib',
            params: 'resources/kit/params',
            routes: 'resources/kit/routes',
            serviceWorker: 'resources/kit/routes',
            appTemplate: 'resources/kit/app.html',
            errorTemplate: 'resources/kit/error.html'
        }
    }
};

export default config;
