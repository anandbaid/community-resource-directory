<script setup>
import DataTable from '@/Components/Admin/DataTable.vue';
import DeleteButton from '@/Components/Admin/DeleteButton.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

defineProps({
    staticPages: { type: Array, default: () => [] },
    createUrl: { type: String, required: true },
});
</script>

<template>
    <PageHeader title="Static Pages" :breadcrumbs="[{ label: 'Home' }, { label: 'Static Pages' }]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Static Pages</h5>
                            <a class="btn btn-primary" :href="createUrl">Add New</a>
                        </div>
                        <div class="card-body">
                            <DataTable
                                :columns="['Page Name', 'Slug', 'Header', 'Footer', 'Status', 'Action']"
                                :rows="staticPages"
                                :search-keys="['title', 'slug', 'status']"
                            >
                                <template #row="{ row }">
                                    <td>{{ row.title }}</td>
                                    <td>{{ row.slug }}</td>
                                    <td>
                                        {{ row.showInHeader ? 'Yes' : 'No' }}
                                        <template v-if="row.showInHeader && row.headerParent">
                                            ({{ row.headerParent }})
                                        </template>
                                    </td>
                                    <td>{{ row.showInFooter ? 'Yes' : 'No' }}</td>
                                    <td>{{ row.status }}</td>
                                    <td>
                                        <a :href="row.editUrl" class="btn btn-primary">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <!--
                                            Pages the site's own routes depend on
                                            stay, and say why, rather than simply
                                            having no button.
                                        -->
                                        <DeleteButton
                                            v-if="row.deletable"
                                            class="ms-1"
                                            :url="row.destroyUrl"
                                            title="Are you sure, you want to delete this page?"
                                        />
                                        <button
                                            v-else
                                            type="button"
                                            class="btn btn-danger ms-1"
                                            disabled
                                            title="Built-in pages cannot be deleted"
                                        >
                                            <i class="fas fa-trash"></i>
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
