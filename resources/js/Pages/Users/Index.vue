<script setup>
import DataTable from '@/Components/Admin/DataTable.vue';
import DeleteButton from '@/Components/Admin/DeleteButton.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import StatusToggle from '@/Components/Admin/StatusToggle.vue';

defineProps({
    users: { type: Array, default: () => [] },
    createUrl: { type: String, required: true },
});

function ucfirst(value) {
    return value ? value.charAt(0).toUpperCase() + value.slice(1) : '';
}
</script>

<template>
    <PageHeader title="Users" :breadcrumbs="[{ label: 'Home' }, { label: 'Users' }]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Users</h5>
                            <a class="btn btn-primary float-sm-end" :href="createUrl">Add New</a>
                            <br class="float-none">
                        </div>
                        <div class="card-body">
                            <DataTable
                                :columns="['Image', 'Name', 'Email', 'Status', 'Action']"
                                :rows="users"
                                :search-keys="['name', 'email', 'status']"
                            >
                                <template #row="{ row }">
                                    <td>
                                        <img
                                            :src="row.avatarUrl"
                                            alt="Profile Image"
                                            width="60"
                                            height="60"
                                            class="profile-image-backend"
                                        >
                                    </td>
                                    <td>{{ row.name }}</td>
                                    <td>{{ row.email }}</td>
                                    <td>{{ ucfirst(row.status) }}</td>
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
                                            title="Are you sure, you want to delete the user?"
                                        />
                                        <StatusToggle
                                            class="ms-1"
                                            :url="row.statusUrl"
                                            :status="row.status"
                                            subject="the user"
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
