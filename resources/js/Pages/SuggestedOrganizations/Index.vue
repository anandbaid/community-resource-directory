<script setup>
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

defineProps({
    suggestions: { type: Array, default: () => [] },
});

const STATUS_CLASS = {
    pending: 'text-bg-warning',
    accepted: 'text-bg-success',
    rejected: 'text-bg-danger',
};
</script>

<template>
    <PageHeader
        title="Suggested Organizations"
        :breadcrumbs="[{ label: 'Home' }, { label: 'Suggested Organizations' }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between">
                            <h5 class="card-title mb-0">Suggested Organizations</h5>
                        </div>
                        <div class="card-body">
                            <DataTable
                                :columns="['Image', 'Name', 'Email', 'Type', 'Status', 'Action']"
                                :rows="suggestions"
                                :search-keys="['name', 'email', 'status']"
                            >
                                <template #row="{ row }">
                                    <td>
                                        <img
                                            :src="row.logo"
                                            alt=""
                                            width="60"
                                            height="60"
                                            class="profile-image-backend"
                                        >
                                    </td>
                                    <td>{{ row.name }}</td>
                                    <td>{{ row.email }}</td>
                                    <td>{{ row.type === 'new' ? 'New entry' : 'Existing organization' }}</td>
                                    <td>
                                        <span class="badge" :class="STATUS_CLASS[row.status]">
                                            {{ row.status }}
                                        </span>
                                    </td>
                                    <td>
                                        <a
                                            :href="row.reviewUrl"
                                            class="btn btn-primary"
                                            :title="row.status === 'pending' ? 'Review' : 'View'"
                                        >
                                            <i
                                                class="fa-solid"
                                                :class="row.status === 'pending' ? 'fa-pen' : 'fa-eye'"
                                            ></i>
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
