import './bootstrap';
import '../css/islands.css';
import { createApp } from 'vue';

/**
 * Public-site entrypoint.
 *
 * Public pages stay server rendered — the resource directory is the crawl path
 * to every /organization-details/{id} page, and this app has no Inertia SSR
 * process available on its hosting. So the frontend runs Vue as *islands*
 * mounted into the Blade markup, and only switches to a full Inertia app on
 * the authenticated account pages that carry a page object.
 *
 * Markup contract:
 *   <div data-vue-island="resource-map" data-vue-props='{"locations":[...]}'></div>
 */
const islands = {
    'resource-search-form': () => import('./Islands/ResourceSearchForm.vue'),
    'resource-map': () => import('./Islands/ResourceMap.vue'),
    'search-result-actions': () => import('./Islands/SearchResultActions.vue'),
    'save-resource-toggle': () => import('./Islands/SaveResourceToggle.vue'),
    'publication-grid': () => import('./Islands/PublicationGrid.vue'),
    'report-spam-modal': () => import('./Islands/ReportSpamModal.vue'),
    'share-modal': () => import('./Islands/ShareModal.vue'),
    'library-filters': () => import('./Islands/LibraryFilters.vue'),
    'contact-form': () => import('./Islands/ContactForm.vue'),
    'career-hub': () => import('./Islands/CareerHub.vue'),
    'team-member-modal': () => import('./Islands/TeamMemberModal.vue'),
};

function mountIslands() {
    document.querySelectorAll('[data-vue-island]').forEach(async (el) => {
        const name = el.dataset.vueIsland;
        const load = islands[name];

        if (!load) {
            console.warn(`Unknown Vue island: ${name}`);

            return;
        }

        let props = {};

        if (el.dataset.vueProps) {
            try {
                props = JSON.parse(el.dataset.vueProps);
            } catch (error) {
                console.error(`Invalid props for Vue island "${name}"`, error);

                return;
            }
        }

        const { default: Component } = await load();

        createApp(Component, props).mount(el);
    });
}

const root = document.getElementById('app');

if (root && root.dataset.page) {
    // Inertia is only pulled in on pages that actually use it.
    import('./lib/inertiaApp').then(({ bootInertia }) => bootInertia());
} else {
    mountIslands();
}
