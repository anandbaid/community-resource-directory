import { reactive } from 'vue';

/**
 * One share sheet per page, driven from anywhere.
 *
 * The Blade partial used to bind jQuery click handlers to `.share-trigger`
 * elements and poke values into the modal's inputs by id. Vue-rendered cards
 * have no such elements to bind at load time, so the modal listens to this
 * store instead — and still picks up legacy `.share-trigger` markup on the
 * pages that have not been converted yet.
 */
export const shareState = reactive({
    // Bumped on every request so repeat shares of the same link still open.
    requests: 0,
    url: '',
    title: 'Share',
    facebook: '',
    twitter: '',
    linkedin: '',
    whatsapp: '',
});

export function openShare(payload = {}) {
    shareState.url = payload.url ?? '';
    shareState.title = payload.title || 'Share';
    shareState.facebook = payload.facebook ?? '';
    shareState.twitter = payload.twitter ?? '';
    shareState.linkedin = payload.linkedin ?? '';
    shareState.whatsapp = payload.whatsapp ?? '';
    shareState.requests += 1;
}

/**
 * Read the payload off a server-rendered `.share-trigger` element.
 */
export function shareFromElement(el) {
    return {
        url: el.dataset.url,
        title: el.dataset.title,
        facebook: el.dataset.facebook,
        twitter: el.dataset.twitter,
        linkedin: el.dataset.linkedin,
        whatsapp: el.dataset.whatsapp,
    };
}
