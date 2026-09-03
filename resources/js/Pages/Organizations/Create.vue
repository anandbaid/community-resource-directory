<script setup>
import OrganizationForm from '@/Components/Organizations/OrganizationForm.vue';

defineProps({
    submitUrl: String,
    indexUrl: String,
    values: Object,
    states: Array,
    publicationStates: Array,
    categories: Array,
    publications: Array,
    placeholderImage: String,
    mapApiKey: String,
    publicationStoreUrl: String,
    ckeditorUrl: String,
});
</script>

<template>
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Create Organization</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a :href="indexUrl">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create Organization</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Organization Details</h4>
                        </div>

                        <OrganizationForm
                            mode="create"
                            :submit-url="submitUrl"
                            :values="values"
                            :states="states"
                            :publication-states="publicationStates"
                            :categories="categories"
                            :publications="publications"
                            :placeholder-image="placeholderImage"
                            :map-api-key="mapApiKey"
                            :publication-store-url="publicationStoreUrl"
                            :ckeditor-url="ckeditorUrl"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
