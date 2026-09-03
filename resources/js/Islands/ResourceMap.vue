<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { loadGoogleMaps } from '@/lib/googleMaps';

const props = defineProps({
    apiKey: { type: String, default: '' },
    // [{ name, lat, lng, state, postal_code, org_id, type }]
    locations: { type: Array, default: () => [] },
    detailsUrl: { type: String, default: '/organization-details' },
    height: { type: String, default: '500px' },
});

const mapEl = ref(null);
const error = ref('');

let map = null;
let markers = [];

onMounted(async () => {
    try {
        const maps = await loadGoogleMaps(props.apiKey);

        map = new maps.Map(mapEl.value, {
            zoom: 5,
            center: { lat: 39, lng: -101 },
            restriction: {
                latLngBounds: { north: 85, south: -85, west: -180, east: 180 },
                strictBounds: true,
            },
            mapTypeControl: true,
            zoomControl: true,
            zoomControlOptions: { position: maps.ControlPosition.RIGHT_CENTER },
            streetViewControl: true,
            fullscreenControl: true,
            minZoom: 3,
        });

        addMarkers(maps, props.locations);

        if (props.locations.length > 0) {
            maps.event.addListenerOnce(map, 'bounds_changed', () => {
                if (map.getZoom() > 14) {
                    map.setZoom(13);
                }
            });
        }
    } catch (e) {
        error.value = e.message;
    }
});

onBeforeUnmount(clearMarkers);

function clearMarkers() {
    markers.forEach((marker) => marker.setMap(null));
    markers = [];
}

function addMarkers(maps, locations) {
    clearMarkers();

    locations.forEach((location) => {
        const lat = parseFloat(location.lat);
        const lng = parseFloat(location.lng);

        if (Number.isNaN(lat) || Number.isNaN(lng)) {
            return;
        }

        // Government pins red, everything else blue — as before. The icon host is
        // forced to https so it is not blocked as mixed content.
        const color = location.type === 'government' ? 'red' : 'blue';

        const marker = new maps.Marker({
            position: { lat, lng },
            map,
            title: location.name || 'Unnamed location',
            icon: {
                url: `https://maps.google.com/mapfiles/ms/icons/${color}-dot.png`,
                scaledSize: new maps.Size(25, 25),
            },
        });

        const heading = document.createElement('h6');

        heading.textContent = 'Success Starts Here!';
        heading.style.margin = '0';
        heading.style.fontSize = '16px';

        const infoWindow = new maps.InfoWindow({
            headerContent: heading,
            content: buildInfoContent(location),
        });

        infoWindow.addListener('domready', () => styleInfoHeader(heading));
        marker.addListener('click', () => infoWindow.open(map, marker));

        markers.push(marker);
    });
}

function buildInfoContent(location) {
    const wrapper = document.createElement('div');

    wrapper.className = 'iw-org-content';
    wrapper.style.minWidth = '150px';
    wrapper.style.paddingRight = '12px';

    const name = document.createElement('strong');

    name.textContent = location.name || 'No name';

    const link = document.createElement('a');

    link.href = `${props.detailsUrl}/${location.org_id}`;
    link.target = '_blank';
    link.rel = 'noopener';
    link.textContent = 'Visit Page';
    link.style.display = 'inline-block';
    link.style.marginTop = '5px';

    wrapper.append(name, document.createElement('br'), link);

    return wrapper;
}

/**
 * The InfoWindow header is rendered by the Maps SDK; nudge its layout once it
 * exists so the custom heading and the close button line up.
 */
function styleInfoHeader(heading) {
    const parent = heading.parentElement;

    if (!parent) {
        return;
    }

    parent.style.padding = '0';
    parent.style.display = 'flex';
    parent.style.alignItems = 'center';

    const headerParent = parent.parentElement;
    const button = headerParent?.querySelector('button');
    const buttonSpan = headerParent?.querySelector('button span');

    if (button) {
        button.style.display = 'flex';
        button.style.alignItems = 'center';
        button.style.justifyContent = 'end';
    }
    if (buttonSpan) {
        buttonSpan.style.margin = 0;
    }
}
</script>

<template>
    <div>
        <div ref="mapEl" :style="{ width: '100%', height }"></div>
        <p v-if="error" class="text-danger mt-2 mb-0">{{ error }}</p>
    </div>
</template>
