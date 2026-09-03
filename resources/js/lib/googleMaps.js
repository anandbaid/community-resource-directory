/**
 * Lazily loads the Google Maps Places library once per page and exposes the
 * address parsing the organization form needs.
 *
 * The Blade forms loaded maps.googleapis.com with a plain <script> tag; Inertia
 * pages mount after that point in the document lifecycle, so the loader is
 * promise based and memoised instead.
 */

let loader = null;

export function loadGoogleMaps(apiKey) {
    if (loader) {
        return loader;
    }

    if (!apiKey) {
        loader = Promise.reject(new Error('Google Maps API key is not configured.'));

        return loader;
    }

    if (window.google?.maps?.places) {
        loader = Promise.resolve(window.google.maps);

        return loader;
    }

    loader = new Promise((resolve, reject) => {
        const script = document.createElement('script');

        script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&libraries=places`;
        script.async = true;
        script.defer = true;
        script.onload = () => {
            if (window.google?.maps?.places) {
                resolve(window.google.maps);
            } else {
                reject(new Error('Google Maps loaded without the places library.'));
            }
        };
        script.onerror = () => reject(new Error('Google Maps failed to load.'));

        document.head.appendChild(script);
    });

    return loader;
}

/**
 * Attach a US-restricted Places autocomplete to an <input>.
 *
 * @returns {Promise<void>}
 */
export function attachAutocomplete(input, onPlace, apiKey) {
    if (!input) {
        return Promise.resolve();
    }

    return loadGoogleMaps(apiKey).then((maps) => {
        const autocomplete = new maps.places.Autocomplete(input, {
            componentRestrictions: { country: 'us' },
        });

        autocomplete.addListener('place_changed', () => {
            onPlace(parsePlace(autocomplete.getPlace()));
        });
    });
}

/**
 * Flatten a Places result into the fields the organization form stores.
 * Component lookup order matches the original Blade implementation.
 */
export function parsePlace(place) {
    if (!place) {
        return null;
    }

    const components = place.address_components || [];
    const find = (type) => components.find((component) => component.types.includes(type));

    const city = find('locality') || find('sublocality') || find('administrative_area_level_2');
    const state = find('administrative_area_level_1');
    const postal = find('postal_code');

    return {
        hasGeometry: Boolean(place.geometry?.location),
        latitude: place.geometry?.location ? place.geometry.location.lat() : null,
        longitude: place.geometry?.location ? place.geometry.location.lng() : null,
        formattedAddress: place.formatted_address || place.name || '',
        city: city ? city.long_name : '',
        stateLong: state ? state.long_name : '',
        stateShort: state ? state.short_name : '',
        postalCode: postal ? postal.long_name : '',
    };
}

/**
 * Resolve a Places state (long or short name) against the app's states list.
 */
export function matchState(options, longName, shortName) {
    const candidates = [longName, shortName].filter(Boolean);

    if (candidates.length === 0) {
        return null;
    }

    const match = options.find((option) => candidates.includes(option));

    return match ?? null;
}
