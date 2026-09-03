<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

import DataTable from '@/Components/Admin/DataTable.vue';
import DeleteButton from '@/Components/Admin/DeleteButton.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

const props = defineProps({
    organizations: { type: Object, required: true },
    filters: { type: Object, default: () => ({ search: '', state: '' }) },
    states: { type: Array, default: () => [] },
    createUrl: { type: String, required: true },
    exportUrl: { type: String, required: true },
    bulkDestroyUrl: { type: String, required: true },
    validateUrl: { type: String, required: true },
});

const state = ref(props.filters.state ?? '');
const selected = ref([]);
const busy = ref(false);

const rows = computed(() => props.organizations.data);
const allSelected = computed(() => rows.value.length > 0 && selected.value.length === rows.value.length);

// A new page of results has nothing to do with the previous page's selection.
watch(rows, () => {
    selected.value = [];
});

function applyState() {
    router.get(
        window.location.pathname,
        {
            ...(props.filters.search ? { search: props.filters.search } : {}),
            ...(state.value ? { state: state.value } : {}),
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function toggleAll(event) {
    selected.value = event.target.checked ? rows.value.map((row) => row.id) : [];
}

async function confirmed(title, text) {
    const result = await window.Swal.fire({
        title,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, do it!',
        cancelButtonText: 'No, cancel!',
        reverseButtons: true,
    });

    return result.isConfirmed;
}

async function bulkDelete() {
    if (!selected.value.length) {
        return;
    }

    const ok = await confirmed(
        `Delete ${selected.value.length} selected organization(s)?`,
        'This action could not be reverted',
    );

    if (!ok) {
        return;
    }

    busy.value = true;

    router.delete(props.bulkDestroyUrl, {
        data: { ids: selected.value },
        preserveScroll: true,
        onFinish: () => {
            busy.value = false;
            selected.value = [];
        },
    });
}

async function toggleStatus(row) {
    const next = row.status === 'active' ? 'inactive' : 'active';
    const ok = await confirmed(
        `Are you sure you want to make this organization ${next}?`,
        next === 'active'
            ? 'Its email and website are checked before it goes live.'
            : 'It will stop appearing in the public directory.',
    );

    if (!ok) {
        return;
    }

    router.patch(row.statusUrl, { status: next }, { preserveScroll: true });
}

async function runValidation() {
    const ok = await confirmed(
        'Run validation for all organizations?',
        'This will validate URL and email for all records and may take a while.',
    );

    if (ok) {
        router.post(props.validateUrl, {}, { preserveScroll: true });
    }
}
</script>

<template>
    <PageHeader title="Organizations" :breadcrumbs="[{ label: 'Home' }, { label: 'Organizations' }]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h5 class="card-title mb-0">Organizations</h5>

                            <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="mb-0" for="state-filter">State</label>
                                    <select
                                        id="state-filter"
                                        v-model="state"
                                        class="form-select form-select-sm"
                                        style="min-width: 180px;"
                                        @change="applyState"
                                    >
                                        <option value="">All</option>
                                        <option v-for="name in states" :key="name" :value="name">{{ name }}</option>
                                    </select>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-danger"
                                    :disabled="!selected.length || busy"
                                    @click="bulkDelete"
                                >
                                    Delete Selected<span v-if="selected.length"> ({{ selected.length }})</span>
                                </button>

                                <a class="btn btn-primary" :href="createUrl">Add New</a>
                                <a class="btn btn-primary" :href="exportUrl">Export</a>

                                <button type="button" class="btn btn-warning" @click="runValidation">
                                    Manual URL and email Verification
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <DataTable
                                :columns="['', 'Image', 'Name', 'Email', 'Report Count', 'Status', 'Action']"
                                :rows="rows"
                                :paginator="organizations"
                                :server-search="filters.search"
                            >
                                <template #head-first>
                                    <input type="checkbox" :checked="allSelected" @change="toggleAll">
                                </template>

                                <template #row="{ row }">
                                    <td>
                                        <input v-model="selected" type="checkbox" class="form-check-input" :value="row.id">
                                    </td>
                                    <td>
                                        <img :src="row.logo" alt="" width="60" height="60" class="profile-image-backend">
                                    </td>
                                    <td>{{ row.name }}</td>
                                    <td>{{ row.email }}</td>
                                    <td>
                                        <a v-if="row.spamCount > 0" :href="row.spamUrl">{{ row.spamCount }}</a>
                                        <template v-else>0</template>
                                    </td>
                                    <td>{{ row.statusLabel }}</td>
                                    <td class="text-nowrap">
                                        <a :href="row.showUrl" class="btn btn-primary" title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a :href="row.editUrl" class="btn btn-primary ms-1" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <DeleteButton
                                            class="ms-1"
                                            :url="row.destroyUrl"
                                            title="Are you sure, you want to delete the organization?"
                                        />
                                        <!--
                                            The Blade table wired this with an
                                            inline onclick="statusUpdate($(this))"
                                            baked into the JSON payload.
                                        -->
                                        <button
                                            type="button"
                                            class="btn ms-1"
                                            :class="row.status === 'active' ? 'btn-secondary' : 'btn-warning'"
                                            :title="row.status === 'active' ? 'Make inactive' : 'Make active'"
                                            @click="toggleStatus(row)"
                                        >
                                            <i class="fas fa-lightbulb icon-spacer"></i>
                                        </button>
                                    </td>
                                </template>
                            </DataTable>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
