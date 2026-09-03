<script setup>
import OrganizationForm from '@/Components/Organizations/OrganizationForm.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

defineProps({
    name: { type: String, default: '' },
    indexUrl: { type: String, required: true },
    editUrl: { type: String, required: true },
    submitUrl: { type: String, required: true },
    values: { type: Object, required: true },
    states: { type: Array, default: () => [] },
    publicationStates: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    publications: { type: Array, default: () => [] },
    logoUrl: { type: String, default: '' },
    currentFileUrl: { type: String, default: '' },
    placeholderImage: { type: String, required: true },
    mapApiKey: { type: String, default: '' },
    publicationStoreUrl: { type: String, required: true },
    ckeditorUrl: { type: String, default: '' },
});
</script>

<template>
    <PageHeader
        title="View Organization"
        :breadcrumbs="[
            { label: 'Home' },
            { label: 'Organizations', url: indexUrl },
            { label: 'View Organization' },
        ]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                            <h5 class="card-title mb-0">{{ name }}</h5>
                            <a :href="editUrl" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>
                        </div>

                        <!--
                            The same form the admin edits with, read-only. This
                            screen used to be a fourth copy of that markup with
                            every input individually disabled.
                        -->
                        <OrganizationForm
                            mode="edit"
                            readonly
                            :submit-url="submitUrl"
                            :values="values"
                            :states="states"
                            :publication-states="publicationStates"
                            :categories="categories"
                            :publications="publications"
                            :logo-url="logoUrl"
                            :current-file-url="currentFileUrl"
                            :placeholder-image="placeholderImage"
                            :map-api-key="mapApiKey"
                            :publication-store-url="publicationStoreUrl"
                            :ckeditor-url="ckeditorUrl"
                        >
                            <template #actions>
                                <a :href="indexUrl" class="btn btn-sm btn-secondary">Back</a>
                            </template>
                        </OrganizationForm>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
