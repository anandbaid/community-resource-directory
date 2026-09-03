<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

import FormRow from '@/Components/Admin/FormRow.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

const props = defineProps({
    type: { type: String, required: true },
    submitUrl: { type: String, required: true },
    indexUrl: { type: String, required: true },
    pages: { type: Array, default: () => [] },
    imageUrl: { type: String, default: '' },
    values: { type: Object, required: true },
});

const isEdit = props.type === 'Edit';

const form = useForm({
    ...(isEdit ? { _method: 'put' } : {}),
    ...props.values,
    banner_image: null,
});

// Only the Home banner carries heading/body copy — the controller ignores (and
// on update, clears) them for every other page.
const isHome = computed(() => form.page_title === 'Home');

function onFileChange(event) {
    form.banner_image = event.target.files[0] ?? null;
}

function submit() {
    form.post(props.submitUrl, { forceFormData: true, preserveScroll: true });
}
</script>

<template>
    <PageHeader
        :title="`${type} Banner`"
        :breadcrumbs="[{ label: 'Banners', url: indexUrl }, { label: `${type} Banner` }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Banner Details</h4>
                        </div>

                        <form class="px-3" @submit.prevent="submit">
                            <div class="card-body">
                                <FormRow label="Page Title:" :error="form.errors.page_title" control-class="col-md-10">
                                    <select v-model="form.page_title" class="form-control" required>
                                        <option value="">Select Page</option>
                                        <option v-for="page in pages" :key="page" :value="page">{{ page }}</option>
                                    </select>
                                </FormRow>

                                <template v-if="isHome">
                                    <FormRow label="Banner Heading:" :error="form.errors.banner_heading" control-class="col-md-10">
                                        <input
                                            v-model="form.banner_heading"
                                            type="text"
                                            class="form-control"
                                            placeholder="Banner Heading"
                                            required
                                        >
                                    </FormRow>

                                    <FormRow label="Banner Text:" :error="form.errors.banner_text" control-class="col-md-10">
                                        <textarea v-model="form.banner_text" class="form-control" placeholder="Enter.." required></textarea>
                                    </FormRow>
                                </template>

                                <FormRow label="Banner Image:" :error="form.errors.banner_image" control-class="col-md-10">
                                    <input type="file" class="form-control" :required="!isEdit" @change="onFileChange">
                                    <div v-if="imageUrl" class="mt-2">
                                        <img :src="imageUrl" alt="Current banner" width="160" height="90">
                                    </div>
                                </FormRow>

                                <FormRow label="Banner Status:" :error="form.errors.status" control-class="col-md-10">
                                    <select v-model="form.status" class="form-control">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </FormRow>

                                <FormRow label="Banner Order:" :error="form.errors.banner_order" control-class="col-md-10">
                                    <input v-model="form.banner_order" type="number" class="form-control">
                                </FormRow>
                            </div>

                            <div class="card-footer d-flex">
                                <button type="submit" class="btn btn-primary mr-2" :disabled="form.processing">
                                    {{ form.processing ? 'Saving…' : 'Submit' }}
                                </button>
                                <a :href="indexUrl" class="btn btn-danger mx-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
