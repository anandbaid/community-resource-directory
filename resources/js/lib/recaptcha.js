/**
 * reCAPTCHA v3 token minting for Vue-rendered forms.
 *
 * The Blade pages get their token from an inline DOMContentLoaded handler in
 * resources/views/frontend/includes/foot.blade.php, which walks the DOM and
 * appends a hidden `recaptcha_token` input to every <form> it finds. That does
 * not work here for two reasons:
 *
 *   1. Vue forms do not exist yet when that handler runs, so they never get a
 *      token and App\Http\Middleware\RecaptchaProtection rejects the request.
 *   2. v3 tokens expire about two minutes after they are minted, so a token
 *      taken at page load is stale by the time anyone finishes a long form.
 *
 * Minting at submit time fixes both.
 */

const SCRIPT_TIMEOUT_MS = 10000;

function siteKey() {
    return window.recaptcha_site_key || '';
}

function ready() {
    return new Promise((resolve, reject) => {
        if (!window.grecaptcha) {
            reject(new Error('reCAPTCHA has not loaded.'));

            return;
        }

        const timer = setTimeout(
            () => reject(new Error('reCAPTCHA timed out.')),
            SCRIPT_TIMEOUT_MS,
        );

        window.grecaptcha.ready(() => {
            clearTimeout(timer);
            resolve();
        });
    });
}

/**
 * Mint a fresh token for a form submission.
 *
 * Resolves to null when reCAPTCHA is unavailable (e.g. no site key configured
 * locally) so callers can still submit — the server remains the authority and
 * will reject the request if it genuinely requires a token.
 *
 * @param {string} action
 * @returns {Promise<string|null>}
 */
export async function recaptchaToken(action = 'page_form') {
    if (!siteKey()) {
        return null;
    }

    try {
        await ready();

        return await window.grecaptcha.execute(siteKey(), { action });
    } catch (error) {
        console.error('Could not obtain a reCAPTCHA token', error);

        return null;
    }
}

/**
 * Wrap an Inertia useForm submit so it carries a fresh token.
 *
 * @param {object} form   Inertia useForm instance with a `recaptcha_token` field
 * @param {function} send Called once the token is in place
 */
export async function withRecaptcha(form, send, action = 'page_form') {
    form.recaptcha_token = await recaptchaToken(action);

    send();
}
