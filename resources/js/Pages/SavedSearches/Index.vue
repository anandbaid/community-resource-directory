<script setup>
import DataTable from '@/Components/Admin/DataTable.vue';
import DeleteButton from '@/Components/Admin/DeleteButton.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

defineProps({
    savedSearches: { type: Object, required: true },
    filters: { type: Object, default: () => ({ search: '' }) },
});
</script>

<template>
    <PageHeader
        title="Manage Saved Searches"
        :breadcrumbs="[{ label: 'Home' }, { label: 'Manage Saved Searches' }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Saved Searches</h5>
                        </div>
                        <div class="card-body">
                            <DataTable
                                :columns="['User', 'Search Parameters', 'Saved On', 'Action']"
                                :rows="savedSearches.data"
                                :paginator="savedSearches"
                                :server-search="filters.search"
                            >
                                <template #row="{ row }">
                                    <td class="align-middle">
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold">{{ row.user.name }}</span>
                                            <span v-if="row.user.email" class="text-muted small">
                                                {{ row.user.email }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="align-middle">{{ row.criteria }}</td>
                                    <td class="align-middle">{{ row.createdAt }}</td>
                                    <td class="align-middle">
                                        <div class="d-flex gap-1">
                                            <a
                                                v-if="row.downloadUrl"
                                                :href="row.downloadUrl"
                                                class="btn btn-primary btn-sm"
                                                title="Download PDF"
                                            >
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                            <span v-else class="text-muted small align-self-center">No file</span>

                                            <DeleteButton
                                                :url="row.destroyUrl"
                                                class="btn-sm"
                                                title="Delete this saved search?"
                                                text="This will remove the saved search and its PDF file."
                                            />
                                        </div>
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
