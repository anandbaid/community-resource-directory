<script setup>
import DataTable from '@/Components/Admin/DataTable.vue';
import DeleteButton from '@/Components/Admin/DeleteButton.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

defineProps({
    publications: { type: Array, default: () => [] },
    createUrl: { type: String, required: true },
});
</script>

<template>
    <PageHeader title="Publications" :breadcrumbs="[{ label: 'Home' }, { label: 'Publications' }]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Publications</h5>
                            <a class="btn btn-primary float-end" :href="createUrl">Add New</a>
                            <br class="float-none">
                        </div>
                        <div class="card-body">
                            <DataTable
                                :columns="['Cover File', 'Publication Title', 'State', 'Description', 'Action']"
                                :rows="publications"
                                :search-keys="['title', 'state', 'description']"
                            >
                                <template #row="{ row }">
                                    <td>
                                        <img
                                            :src="row.imageUrl"
                                            alt="Cover"
                                            width="60"
                                            height="60"
                                            class="profile-image-backend"
                                        >
                                    </td>
                                    <td>{{ row.title }}</td>
                                    <td>{{ row.state }}</td>
                                    <td class="lib-desc">
                                        <div class="clamp-2">{{ row.description }}</div>
                                    </td>
                                    <td>
                                        <a :href="row.showUrl" class="btn btn-primary">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a :href="row.editUrl" class="btn btn-primary ms-1">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <DeleteButton
                                            class="ms-1"
                                            :url="row.deleteUrl"
                                            title="Are you sure, you want to delete the publication?"
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
