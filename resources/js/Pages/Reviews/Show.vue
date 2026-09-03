<script setup>
import PageHeader from '@/Components/Admin/PageHeader.vue';

defineProps({
    review: { type: Object, required: true },
    // [{ question, answer }] — built server side so the questionnaire shape
    // lives in one place.
    answers: { type: Array, default: () => [] },
    indexUrl: { type: String, required: true },
});
</script>

<template>
    <PageHeader
        title="Review Details"
        :breadcrumbs="[{ label: 'Reviews', url: indexUrl }, { label: 'Review Details' }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Review Details</h4>
                        </div>

                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label mx-1 fw-bold">User:</label>{{ review.user }}
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mx-1 fw-bold">Organization:</label>{{ review.organization }}
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mx-1 fw-bold">Agency/Organization Rating:</label>
                                    <i v-for="star in review.rate" :key="star" class="bi bi-star-fill"></i>
                                </div>
                            </div>

                            <hr>

                            <dl class="row mb-0">
                                <template v-for="(item, index) in answers" :key="index">
                                    <dt class="col-md-8 fw-normal py-2">{{ item.question }}</dt>
                                    <dd class="col-md-4 py-2">{{ item.answer || '—' }}</dd>
                                </template>
                            </dl>
                        </div>

                        <div class="card-footer">
                            <a :href="indexUrl" class="btn btn-sm btn-primary">Back to reviews</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
