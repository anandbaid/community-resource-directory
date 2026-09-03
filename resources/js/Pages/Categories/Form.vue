<script setup>
import { useForm } from '@inertiajs/vue3';

import FormRow from '@/Components/Admin/FormRow.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

const props = defineProps({
    type: { type: String, required: true },
    submitUrl: { type: String, required: true },
    indexUrl: { type: String, required: true },
    values: { type: Object, required: true },
});

const isEdit = props.type === 'Edit';

const form = useForm({
    ...(isEdit ? { _method: 'put' } : {}),
    ...props.values,
});

function submit() {
    form.post(props.submitUrl, { preserveScroll: true });
}
</script>

<template>
    <PageHeader
        :title="`${type} Category`"
        :breadcrumbs="[{ label: 'Categories', url: indexUrl }, { label: `${type} Category` }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12 col-md-8 col-sm-10 col-10">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0 text-center">{{ type }} Category</h4>
                        </div>
                        <div class="card-body">
                            <form class="px-3" @submit.prevent="submit">
                                <FormRow label="Category Name:" :error="form.errors.name">
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="form-control"
                                        required
                                        placeholder="Enter a Category Name"
                                    >
                                </FormRow>

                                <FormRow label="Status:" :error="form.errors.status" control-class="col-md-2">
                                    <select v-model="form.status" class="form-control" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </FormRow>

                                <FormRow label="Order:" :error="form.errors.category_order">
                                    <input
                                        v-model="form.category_order"
                                        type="number"
                                        class="form-control"
                                        required
                                    >
                                </FormRow>

                                <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                        {{ form.processing ? 'Saving…' : 'Save' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
