import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';

import { flashFromProps } from './flash';

const pages = import.meta.glob('../Pages/**/*.vue');

/**
 * Boot the Inertia client. Shared by the admin and public entrypoints — page
 * components are namespaced by folder, so one registry serves both.
 */
export function bootInertia() {
    createInertiaApp({
        // The Blade chrome already renders the page <title>, so leave it alone.
        title: (title) => title,

        resolve: (name) => {
            const page = pages[`../Pages/${name}.vue`];

            if (!page) {
                throw new Error(`Inertia page not found: ../Pages/${name}.vue`);
            }

            return page();
        },

        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .mount(el);

            // Session flash carried by the very first (server rendered) response.
            flashFromProps(props.initialPage.props.flash);
        },

        progress: {
            color: '#0d6efd',
        },
    });

    // ...and by every subsequent Inertia visit. Shared by HandleInertiaRequests
    // and raised with the same SweetAlert helpers the Blade pages use.
    router.on('success', (event) => {
        flashFromProps(event.detail.page.props.flash);
    });
}
