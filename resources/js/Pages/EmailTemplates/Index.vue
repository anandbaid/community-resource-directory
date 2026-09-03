<script setup>
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import StatusToggle from '@/Components/Admin/StatusToggle.vue';

defineProps({
    emailTemplates: { type: Array, default: () => [] },
    createUrl: { type: String, required: true },
});

function ucfirst(value) {
    return value ? value.charAt(0).toUpperCase() + value.slice(1) : '';
}
</script>

<template>
    <PageHeader
        title="Email Templates"
        :breadcrumbs="[{ label: 'Home' }, { label: 'Email Templates' }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Email Templates</h5>
                            <a class="btn btn-primary float-sm-end" :href="createUrl">Add New</a>
                            <br class="float-none">
                        </div>
                        <div class="card-body">
                            <DataTable
                                :columns="['Name', 'Title', 'Status', 'Action']"
                                :rows="emailTemplates"
                                :search-keys="['name', 'title', 'status']"
                            >
                                <template #row="{ row }">
                                    <td>{{ row.name }}</td>
                                    <td>{{ row.title }}</td>
                                    <td>{{ ucfirst(row.status) }}</td>
                                    <td>
                                        <a :href="row.editUrl" class="btn btn-primary">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <StatusToggle
                                            class="ms-1"
                                            :url="row.statusUrl"
                                            :status="row.status"
                                            subject="this template"
                                        />
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
