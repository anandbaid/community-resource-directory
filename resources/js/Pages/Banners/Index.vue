<script setup>
import DataTable from '@/Components/Admin/DataTable.vue';
import DeleteButton from '@/Components/Admin/DeleteButton.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

defineProps({
    banners: { type: Object, required: true },
    createUrl: { type: String, required: true },
});

function ucfirst(value) {
    return value ? value.charAt(0).toUpperCase() + value.slice(1) : '';
}
</script>

<template>
    <PageHeader title="Banners" :breadcrumbs="[{ label: 'Home' }, { label: 'Banners' }]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Banners</h5>
                            <a class="btn btn-primary float-sm-end" :href="createUrl">Add New</a>
                            <br class="float-none">
                        </div>
                        <div class="card-body">
                            <DataTable
                                :columns="['Page Name', 'Image', 'Status', 'Action']"
                                :rows="banners.data"
                                :paginator="banners"
                            >
                                <template #row="{ row }">
                                    <td>{{ row.page_title }}</td>
                                    <td><img :src="row.imageUrl" alt="Image" width="100" height="60"></td>
                                    <td>{{ ucfirst(row.status) }}</td>
                                    <td>
                                        <a :href="row.editUrl" class="btn btn-primary">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <DeleteButton
                                            class="ms-1"
                                            :url="row.deleteUrl"
                                            title="Are you sure, you want to delete the banner?"
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
