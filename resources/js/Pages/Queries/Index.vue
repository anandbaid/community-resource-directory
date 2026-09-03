<script setup>
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

defineProps({
    queries: { type: Array, default: () => [] },
});
</script>

<template>
    <PageHeader title="Queries" :breadcrumbs="[{ label: 'Home' }, { label: 'Queries' }]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between">
                            <h5 class="card-title mb-0">Queries</h5>
                        </div>
                        <div class="card-body">
                            <DataTable
                                :columns="['#', 'Name', 'Email', 'Organization', 'Action']"
                                :rows="queries"
                                :search-keys="['name', 'email', 'organization']"
                            >
                                <template #row="{ row }">
                                    <td>{{ row.id }}</td>
                                    <td>{{ row.name }}</td>
                                    <td>{{ row.email }}</td>
                                    <td>{{ row.organization }}</td>
                                    <td>
                                        <a :href="row.viewUrl" class="btn btn-primary">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
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
