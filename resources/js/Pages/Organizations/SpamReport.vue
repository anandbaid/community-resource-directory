<script setup>
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

const props = defineProps({
    reports: { type: Array, default: () => [] },
    // Set when the page was opened for one organization.
    organization: { type: Object, default: null },
});

const title = props.organization
    ? `Spam Reports — ${props.organization.name}`
    : 'Spam Reports';
</script>

<template>
    <PageHeader :title="title" :breadcrumbs="[{ label: 'Home' }, { label: 'Spam Reports' }]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ title }}</h5>
                        </div>
                        <div class="card-body">
                            <DataTable
                                :columns="['User Details', 'Organization Details', 'Reason', 'Action']"
                                :rows="reports"
                                :search-keys="['reason']"
                            >
                                <template #row="{ row }">
                                    <td>
                                        {{ row.user.name }}
                                        <div v-if="row.user.email">
                                            <i class="fa-solid fa-envelope"></i>: {{ row.user.email }}
                                        </div>
                                    </td>
                                    <td>
                                        {{ row.organization.name }}
                                        <div>Status: {{ row.organization.status }}</div>
                                    </td>
                                    <td>{{ row.reason }}</td>
                                    <td>
                                        <a
                                            :href="row.organization.editUrl"
                                            class="btn btn-primary"
                                            title="Edit Organization"
                                        ><i class="fa-solid fa-landmark"></i></a>
                                        <a
                                            :href="row.user.editUrl"
                                            class="btn btn-primary ms-1"
                                            title="Edit User"
                                        ><i class="fa-solid fa-user"></i></a>
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
