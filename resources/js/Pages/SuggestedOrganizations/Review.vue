<script setup>
import { computed, ref } from 'vue';

import OrganizationForm from '@/Components/Organizations/OrganizationForm.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import PublicationRows from '@/Components/Publications/PublicationRows.vue';

const props = defineProps({
    indexUrl: { type: String, required: true },
    submitUrl: { type: String, required: true },
    suggestion: { type: Object, required: true },
    readonly: { type: Boolean, default: false },
    values: { type: Object, required: true },
    publicationRows: { type: Array, default: () => [] },
    logoUrl: { type: String, default: '' },
    states: { type: Array, default: () => [] },
    publicationStates: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    placeholderImage: { type: String, required: true },
    mapApiKey: { type: String, default: '' },
    publicationStoreUrl: { type: String, required: true },
    ckeditorUrl: { type: String, default: '' },
});

const organizationForm = ref(null);

// Rows the visitor submitted, edited in place before the suggestion is accepted.
const rows = ref(props.publicationRows.map((row) => ({ ...row })));

const heading = computed(() => (props.readonly ? 'View Suggestion' : 'Review Suggestion'));

const typeLabel = computed(() => (
    props.suggestion.type === 'new' ? 'New entry' : 'Existing organization'
));

/**
 * OrganizationForm owns the payload; the publication rows live here, so they
 * are folded in as the flat `publication_title[0]` shape the controller reads.
 */
function withPublications(payload) {
    const flat = { ...payload };

    rows.value.forEach((row, index) => {
        flat[`publication_title[${index}]`] = row.publication_title ?? '';
        flat[`publication_description[${index}]`] = row.publication_description ?? '';
        flat[`publication_state[${index}]`] = row.publication_state ?? '';
        flat[`publication_update_existing[${index}]`] = row.publication_update_existing ? 1 : 0;
        flat[`cover_file_path[${index}]`] = row.cover_file_path ?? '';
        flat[`cover_image_path[${index}]`] = row.cover_image_path ?? '';

        if (row.cover_file) {
            flat[`cover_file[${index}]`] = row.cover_file;
        }
        if (row.cover_image) {
            flat[`cover_image[${index}]`] = row.cover_image;
        }
    });

    return flat;
}

function decide(decision) {
    organizationForm.value.form.form_type = decision;
    organizationForm.value.submit();
}
</script>

<template>
    <PageHeader
        :title="heading"
        :breadcrumbs="[
            { label: 'Home' },
            { label: 'Suggested Organizations', url: indexUrl },
            { label: heading },
        ]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                            <h5 class="card-title mb-0">{{ values.organization_name }}</h5>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge text-bg-secondary">{{ typeLabel }}</span>
                                <span class="badge text-bg-light">{{ suggestion.status }}</span>
                            </div>
                        </div>

                        <div v-if="readonly" class="alert alert-info mb-0 rounded-0">
                            This suggestion was already {{ suggestion.status }}, so it is read-only.
                        </div>

                        <OrganizationForm
                            ref="organizationForm"
                            mode="review"
                            :readonly="readonly"
                            :submit-url="submitUrl"
                            :values="values"
                            :extra-values="{ form_type: 'accepted', suggestion_type: values.suggestion_type }"
                            :transform="withPublications"
                            :states="states"
                            :publication-states="publicationStates"
                            :categories="categories"
                            :publications="[]"
                            :logo-url="logoUrl"
                            :placeholder-image="placeholderImage"
                            :map-api-key="mapApiKey"
                            :publication-store-url="publicationStoreUrl"
                            :ckeditor-url="ckeditorUrl"
                        >
                            <!--
                                Admin create/edit assigns existing publications;
                                here the rows the visitor typed are edited directly.
                            -->
                            <template #publications>
                                <div class="w-100">
                                    <PublicationRows
                                        v-model="rows"
                                        variant="admin"
                                        :publication-states="publicationStates"
                                        :readonly="readonly"
                                    />
                                </div>
                            </template>

                            <template #actions="{ form }">
                                <template v-if="!readonly">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-success save-btn"
                                        :disabled="form.processing"
                                        @click="decide('accepted')"
                                    >
                                        Accept
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger save-btn ms-2"
                                        :disabled="form.processing"
                                        @click="decide('rejected')"
                                    >
                                        Reject
                                    </button>
                                </template>
                                <a v-else :href="indexUrl" class="btn btn-sm btn-secondary">Back</a>
                            </template>
                        </OrganizationForm>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
