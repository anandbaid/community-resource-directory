<script setup>
import { ref } from 'vue';
import { swalAlert } from '@/lib/flash';

const props = defineProps({
    sort: { type: String, default: 'az' },
    // Save Search is only offered to signed-in, non-admin users with a result set.
    canSave: { type: Boolean, default: false },
    saveUrl: { type: String, default: '' },
    searchParams: { type: Object, default: () => ({}) },
    resultIds: { type: Array, default: () => [] },
});

const sortValue = ref(props.sort);
const saving = ref(false);

const hasSearch = Object.keys(props.searchParams).length > 0;

function onSortChange() {
    const url = new URL(window.location.href);

    if (sortValue.value) {
        url.searchParams.set('sort', sortValue.value);
    } else {
        url.searchParams.delete('sort');
    }

    window.location.href = url.toString();
}

async function saveSearch() {
    saving.value = true;

    try {
        const { data } = await window.axios.post(
            props.saveUrl,
            {
                search: JSON.stringify(props.searchParams),
                ids: props.resultIds.join(', '),
            },
            { headers: { 'X-CSRF-TOKEN': window.csrf_token } },
        );

        swalAlert(data.status, data.message, 1500);
    } catch (error) {
        swalAlert('error', error.response?.data?.errors ?? 'An unexpected error occurred.', 9000);
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div class="search-result-actions">
        <div class="search-sort">
            <select v-model="sortValue" name="sort" @change="onSortChange">
                <option value="az">A-Z</option>
                <option value="za">Z-A</option>
            </select>
        </div>

        <button
            v-if="canSave && hasSearch"
            type="button"
            class="save-search-btn"
            :disabled="saving"
            @click="saveSearch"
        >
            <i class="fa-regular fa-star search-star"></i>
            {{ saving ? 'Saving…' : 'Save Search Result' }}
        </button>
    </div>
</template>
