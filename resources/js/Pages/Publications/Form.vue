<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

import CkeditorField from '@/Components/Admin/CkeditorField.vue';
import FormRow from '@/Components/Admin/FormRow.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

const props = defineProps({
    type: { type: String, required: true },
    submitUrl: { type: String, required: true },
    indexUrl: { type: String, required: true },
    states: { type: Array, default: () => [] },
    organizations: { type: Array, default: () => [] },
    ckeditorUrl: { type: String, required: true },
    fileUrl: { type: String, default: '' },
    imageUrl: { type: String, default: '' },
    values: { type: Object, required: true },
});

const isEdit = props.type === 'Edit';

const form = useForm({
    ...(isEdit ? { _method: 'put' } : {}),
    ...props.values,
    cover_image: null,
    publication_file: null,
});

const coverInput = ref(null);
const fileInput = ref(null);
const coverPreview = ref(props.imageUrl || '/assets/img/placeholder.png');
const fileName = ref(props.fileUrl);

function onCoverChange(event) {
    const file = event.target.files[0] ?? null;

    form.cover_image = file;
    coverPreview.value = file
        ? URL.createObjectURL(file)
        : props.imageUrl || '/assets/img/placeholder.png';
}

function onFileChange(event) {
    const file = event.target.files[0] ?? null;

    form.publication_file = file;
    fileName.value = file ? file.name : props.fileUrl;
}

function submit() {
    form.post(props.submitUrl, { forceFormData: true, preserveScroll: true });
}
</script>

<template>
    <PageHeader
        :title="`${type} Publication`"
        :breadcrumbs="[{ label: 'Publications', url: indexUrl }, { label: `${type} Publication` }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0 text-center">{{ type }} Publication</h4>
                        </div>

                        <div class="card-body">
                            <form class="px-3" @submit.prevent="submit">
                                <FormRow label="Title:" :error="form.errors.title">
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        class="form-control"
                                        placeholder="Enter a Publication title"
                                        required
                                    >
                                </FormRow>

                                <FormRow
                                    label="Organizations:"
                                    :error="form.errors.organization_ids"
                                    control-class="col-md-8"
                                >
                                    <select v-model="form.organization_ids" class="form-select" multiple size="8">
                                        <option
                                            v-for="organization in organizations"
                                            :key="organization.id"
                                            :value="organization.id"
                                        >
                                            {{ organization.name }}
                                        </option>
                                    </select>
                                    <small class="text-muted">Hold Ctrl (Cmd on Mac) to select more than one.</small>
                                </FormRow>

                                <FormRow
                                    label="Publications by State:"
                                    :error="form.errors.state"
                                    control-class="col-md-3"
                                >
                                    <select v-model="form.state" class="form-select" required>
                                        <option value="">Select State</option>
                                        <option value="national">National</option>
                                        <option v-for="state in states" :key="state.id" :value="state.id">
                                            {{ state.name }}
                                        </option>
                                    </select>
                                </FormRow>

                                <FormRow label="Descriptions:" :error="form.errors.description">
                                    <CkeditorField v-model="form.description" :ckeditor-url="ckeditorUrl" />
                                </FormRow>

                                <FormRow label="Publication File:" :error="form.errors.publication_file">
                                    <div class="mt-2">
                                        <input ref="fileInput" type="file" class="d-none" @change="onFileChange">
                                        <button type="button" class="btn btn-primary" @click="fileInput.click()">
                                            <i class="fas fa-upload"></i> Upload File
                                        </button>
                                    </div>
                                    <span>{{ fileName }}</span>
                                </FormRow>

                                <FormRow label="Cover Image:" :error="form.errors.cover_image">
                                    <img
                                        :src="coverPreview"
                                        alt=""
                                        class="img-responsive mt-2"
                                        width="128"
                                        height="128"
                                    >
                                    <div class="mt-2">
                                        <input
                                            ref="coverInput"
                                            type="file"
                                            class="d-none"
                                            accept="image/*"
                                            @change="onCoverChange"
                                        >
                                        <button type="button" class="btn btn-primary" @click="coverInput.click()">
                                            <i class="fas fa-upload"></i> Upload
                                        </button>
                                    </div>
                                </FormRow>

                                <FormRow label="Publication Url:" :error="form.errors.publication_url">
                                    <input v-model="form.publication_url" type="url" class="form-control">
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
