<script setup>
import DataTable from '@/Components/Admin/DataTable.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

defineProps({
    reviews: { type: Object, required: true },
});
</script>

<template>
    <PageHeader title="Reviews" :breadcrumbs="[{ label: 'Home' }, { label: 'Reviews' }]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between">
                            <h5 class="card-title mb-0">Reviews</h5>
                        </div>
                        <div class="card-body">
                            <DataTable
                                :columns="['User', 'Organization', 'Rating', 'Action']"
                                :rows="reviews.data"
                                :paginator="reviews"
                            >
                                <template #row="{ row }">
                                    <td>{{ row.user }}</td>
                                    <td>{{ row.organization }}</td>
                                    <td>
                                        <i v-for="star in row.rate" :key="star" class="bi bi-star-fill"></i>
                                    </td>
                                    <td>
                                        <a :href="row.showUrl" class="btn btn-primary">
                                            <i class="fa-solid fa-eye"></i>
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
