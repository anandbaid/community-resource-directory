<script setup>
import { useForm } from '@inertiajs/vue3';

import CkeditorField from '@/Components/Admin/CkeditorField.vue';
import FieldError from '@/Components/FieldError.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

const props = defineProps({
    type: { type: String, required: true },
    submitUrl: { type: String, required: true },
    indexUrl: { type: String, required: true },
    ckeditorUrl: { type: String, required: true },
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

function reset() {
    form.reset();
}
</script>

<template>
    <PageHeader
        :title="`${type} Template`"
        :breadcrumbs="[{ label: 'Email Templates', url: indexUrl }, { label: `${type} Template` }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Email Template Details</h4>
                        </div>

                        <form class="px-3" @submit.prevent="submit">
                            <div class="card-body">
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Template Name: </label>
                                    </div>
                                    <div class="col-md-10">
                                        <input
                                            v-model="form.template_name"
                                            type="text"
                                            class="form-control"
                                            placeholder="Template Name"
                                            required
                                        >
                                        <FieldError :message="form.errors.template_name" />
                                    </div>
                                </div>

                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Template Subject: </label>
                                    </div>
                                    <div class="col-md-10">
                                        <input
                                            v-model="form.template_title"
                                            type="text"
                                            class="form-control"
                                            placeholder="Template Title"
                                            required
                                        >
                                        <FieldError :message="form.errors.template_title" />
                                    </div>
                                </div>

                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Template Content: </label>
                                    </div>
                                    <div class="col-md-10">
                                        <CkeditorField
                                            v-model="form.ckeditor_data"
                                            :ckeditor-url="ckeditorUrl"
                                        />
                                        <FieldError :message="form.errors.ckeditor_data" />
                                    </div>
                                </div>

                                <div class="form-group row align-center">
                                    <div class="col-md-2">
                                        <label class="form-label">Template Data: </label>
                                    </div>
                                    <div class="col-md-10">
                                        <input
                                            v-model="form.template_data"
                                            type="text"
                                            class="form-control"
                                            placeholder="Template Data"
                                            autocomplete="off"
                                            :required="!isEdit"
                                            :disabled="isEdit"
                                        >
                                        <FieldError :message="form.errors.template_data" />
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer d-flex">
                                <button type="submit" class="btn btn-primary mr-2" :disabled="form.processing">
                                    {{ form.processing ? 'Saving…' : 'Submit' }}
                                </button>
                                <button type="button" class="btn btn-danger mx-2" @click="reset">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
