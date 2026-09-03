<script setup>
import { ref } from 'vue';

const props = defineProps({
    baseUrl: { type: String, required: true },
    // The two controls sit in different columns of the page, so the island is
    // mounted twice and renders one half each. They need no shared client state:
    // both read the current selection from the server and navigate.
    section: { type: String, default: 'sort', validator: (v) => ['sort', 'locations'].includes(v) },
    states: { type: Array, default: () => [] },
    selectedState: { type: String, default: '' },
    order: { type: String, default: 'asc' },
});

const order = ref(props.order || 'asc');

/**
 * Results stay server rendered — this is the crawl path to every publication —
 * so the filters navigate rather than fetch.
 */
function go(state, sortOrder) {
    const url = new URL(props.baseUrl, window.location.origin);

    if (state) {
        url.searchParams.set('state', state);
    }

    if (sortOrder && sortOrder !== 'asc') {
        url.searchParams.set('order', sortOrder);
    }

    window.location.href = url.toString();
}

function toggleState(value) {
    // Clicking the active location clears it, as before.
    go(props.selectedState === value ? '' : value, order.value);
}
</script>

<template>
    <div v-if="section === 'sort'" class="library-select">
        <select v-model="order" aria-label="Sort order" @change="go(selectedState, order)">
            <option value="asc">A-Z</option>
            <option value="desc">Z-A</option>
        </select>
        <button type="button" class="primary-btn reset-filter-btn" @click="go('', 'asc')">
            Reset Filters
        </button>
    </div>

    <ul v-else class="sort-list">
        <li
            class="state-selector national-option"
            :class="{ active: selectedState === 'national' }"
            role="button"
            tabindex="0"
            @click="toggleState('national')"
            @keydown.enter.prevent="toggleState('national')"
            @keydown.space.prevent="toggleState('national')"
        >
            National
        </li>
        <li
            v-for="item in states"
            :key="item.id"
            class="state-selector"
            :class="{ active: selectedState === String(item.id) }"
            role="button"
            tabindex="0"
            @click="toggleState(String(item.id))"
            @keydown.enter.prevent="toggleState(String(item.id))"
            @keydown.space.prevent="toggleState(String(item.id))"
        >
            {{ item.name }}
        </li>
    </ul>
</template>
