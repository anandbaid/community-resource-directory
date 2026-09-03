const loaders = new Map();

/**
 * Load a classic <script> once per page and resolve when it is ready.
 *
 * Used for the legacy globals under public/plugins that Blade pages pull in
 * with @push('custom-scripts') — Inertia pages mount too late for that.
 */
export function loadScript(src) {
    if (loaders.has(src)) {
        return loaders.get(src);
    }

    const loader = new Promise((resolve, reject) => {
        const script = document.createElement('script');

        script.src = src;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error(`Failed to load script: ${src}`));

        document.head.appendChild(script);
    });

    loaders.set(src, loader);

    return loader;
}
