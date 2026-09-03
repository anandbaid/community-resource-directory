<script setup>
import FormRow from '@/Components/Admin/FormRow.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

defineProps({
    query: { type: Object, required: true },
    indexUrl: { type: String, required: true },
});
</script>

<template>
    <PageHeader
        title="Query Details"
        :breadcrumbs="[{ label: 'Queries', url: indexUrl }, { label: 'Query Details' }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12 col-md-8 col-sm-10 col-10">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0 text-center">Query Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="px-3">
                                <FormRow label="User First Name:">
                                    <input type="text" class="form-control" :value="query.first_name" disabled>
                                </FormRow>
                                <FormRow label="User Last Name:">
                                    <input type="text" class="form-control" :value="query.last_name" disabled>
                                </FormRow>
                                <FormRow label="Email:">
                                    <input type="text" class="form-control" :value="query.email" disabled>
                                </FormRow>
                                <FormRow label="Organization:">
                                    <input type="text" class="form-control" :value="query.organization" disabled>
                                </FormRow>
                                <FormRow label="Message:">
                                    <textarea class="form-control" cols="30" rows="10" disabled>{{ query.message }}</textarea>
                                </FormRow>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
