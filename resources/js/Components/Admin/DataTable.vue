<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

import Pagination from '@/Components/Admin/Pagination.vue';

const props = defineProps({
    // Header labels, in order. Also drives the empty-state colspan.
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    // Row keys the search box matches against. Empty = search disabled.
    searchKeys: { type: Array, default: () => [] },
    rowKey: { type: String, default: 'id' },
    emptyText: { type: String, default: 'No record found.' },
    pageLength: { type: Number, default: 10 },
    lengthMenu: { type: Array, default: () => [10, 25, 50, 0] },
    // Laravel paginator payload. When present the server owns paging and the
    // client-side pager is skipped.
    paginator: { type: Object, default: null },
    /**
     * Current server-side search term. Set this instead of `searchKeys` when the
     * server paginates: filtering only the rows on the current page would search
     * ten records and call it a result set.
     */
    serverSearch: { type: String, default: null },
});

const search = ref(props.serverSearch ?? '');
const perPage = ref(props.pageLength);
const page = ref(1);

const serverPaged = computed(() => props.paginator !== null);
const serverSearchable = computed(() => props.serverSearch !== null);
const searchable = computed(() => serverSearchable.value || props.searchKeys.length > 0);

let searchTimer = null;

/** Debounced so typing does not fire a request per keystroke. */
function onServerSearch() {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        router.get(
            window.location.pathname,
            search.value ? { search: search.value } : {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
}

const filtered = computed(() => {
    const term = search.value.trim().toLowerCase();

    // The server already applied the filter.
    if (serverSearchable.value) {
        return props.rows;
    }

    if (!term || !searchable.value) {
        return props.rows;
    }

    return props.rows.filter((row) =>
        props.searchKeys.some((key) => String(row[key] ?? '').toLowerCase().includes(term)),
    );
});

const totalPages = computed(() => {
    if (serverPaged.value || perPage.value === 0) {
        return 1;
    }

    return Math.max(1, Math.ceil(filtered.value.length / perPage.value));
});

const visible = computed(() => {
    if (serverPaged.value || perPage.value === 0) {
        return filtered.value;
    }

    const start = (page.value - 1) * perPage.value;

    return filtered.value.slice(start, start + perPage.value);
});

const summary = computed(() => {
    if (serverPaged.value) {
        return `Showing ${props.paginator.from ?? 0} to ${props.paginator.to ?? 0} of ${props.paginator.total} entries`;
    }

    const total = filtered.value.length;

    if (total === 0) {
        return 'Showing 0 entries';
    }

    const start = perPage.value === 0 ? 1 : (page.value - 1) * perPage.value + 1;

    return `Showing ${start} to ${start + visible.value.length - 1} of ${total} entries`;
});

// Narrowing the result set can strand the viewer past the last page.
watch([filtered, perPage], () => {
    if (page.value > totalPages.value) {
        page.value = 1;
    }
});

function lengthLabel(length) {
    return length === 0 ? 'All' : String(length);
}
</script>

<template>
    <div>
        <div v-if="searchable || !serverPaged" class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <label v-if="!serverPaged" class="d-flex align-items-center gap-2 mb-0">
                <span>Show</span>
                <select v-model.number="perPage" class="form-select form-select-sm w-auto">
                    <option v-for="length in lengthMenu" :key="length" :value="length">{{ lengthLabel(length) }}</option>
                </select>
                <span>entries</span>
            </label>
            <span v-else></span>

            <label v-if="searchable" class="d-flex align-items-center gap-2 mb-0">
                <span>Search:</span>
                <input
                    v-model="search"
                    type="search"
                    class="form-control form-control-sm w-auto"
                    @input="serverSearchable && onServerSearch()"
                >
            </label>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th v-for="(column, index) in columns" :key="index" scope="col">
                            <slot v-if="index === 0" name="head-first">{{ column }}</slot>
                            <template v-else>{{ column }}</template>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, index) in visible" :key="row[rowKey] ?? index">
                        <slot name="row" :row="row" :index="index" />
                    </tr>

                    <tr v-if="visible.length === 0">
                        <td :colspan="columns.length" class="text-center">{{ emptyText }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small class="text-muted">{{ summary }}</small>

            <Pagination v-if="serverPaged" :links="paginator.links" />

            <nav v-else-if="totalPages > 1" aria-label="Table pages">
                <ul class="pagination mb-0">
                    <li class="page-item" :class="{ disabled: page === 1 }">
                        <button type="button" class="page-link" @click="page -= 1">Previous</button>
                    </li>
                    <li v-for="p in totalPages" :key="p" class="page-item" :class="{ active: p === page }">
                        <button type="button" class="page-link" @click="page = p">{{ p }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: page === totalPages }">
                        <button type="button" class="page-link" @click="page += 1">Next</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>
