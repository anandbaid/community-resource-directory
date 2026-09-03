<script setup>
import DataTable from '@/Components/Admin/DataTable.vue';
import DeleteButton from '@/Components/Admin/DeleteButton.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

defineProps({
    categories: { type: Object, required: true },
    createUrl: { type: String, required: true },
});
</script>

<template>
    <PageHeader
        title="Service Categories"
        :breadcrumbs="[{ label: 'Home' }, { label: 'Service Categories' }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Categories</h5>
                            <a class="btn btn-primary float-sm-end" :href="createUrl">Add New</a>
                            <br class="float-none">
                        </div>
                        <div class="card-body">
                            <DataTable
                                :columns="['Category Name', 'Category Order', 'Status', 'Action']"
                                :rows="categories.data"
                                :paginator="categories"
                            >
                                <template #row="{ row }">
                                    <td>{{ row.name }}</td>
                                    <td>{{ row.category_order }}</td>
                                    <td>{{ row.status }}</td>
                                    <td>
                                        <a :href="row.editUrl" class="btn btn-primary">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <DeleteButton
                                            class="ms-1"
                                            :url="row.deleteUrl"
                                            title="Are you sure, you want to delete the category?"
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
