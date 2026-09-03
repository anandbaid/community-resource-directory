<script setup>
import { computed } from 'vue';

import FormRow from '@/Components/Admin/FormRow.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

const props = defineProps({
    values: { type: Object, required: true },
    states: { type: Array, default: () => [] },
    organizations: { type: Array, default: () => [] },
    fileUrl: { type: String, default: '' },
    imageUrl: { type: String, default: '' },
    indexUrl: { type: String, required: true },
    editUrl: { type: String, required: true },
});

const stateLabel = computed(() => {
    if (props.values.state === 'national') {
        return 'National';
    }

    return props.states.find((state) => state.id === props.values.state)?.name ?? '';
});

const assignedOrganizations = computed(() =>
    props.organizations
        .filter((organization) => props.values.organization_ids.includes(organization.id))
        .map((organization) => organization.name),
);
</script>

<template>
    <PageHeader
        title="Publication Details"
        :breadcrumbs="[{ label: 'Publications', url: indexUrl }, { label: 'Publication Details' }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0 text-center">Publication Details</h4>
                        </div>

                        <div class="card-body px-3">
                            <FormRow label="Title:">
                                <p class="form-control-plaintext mb-0">{{ values.title }}</p>
                            </FormRow>

                            <FormRow label="Organizations:">
                                <p class="form-control-plaintext mb-0">
                                    {{ assignedOrganizations.length ? assignedOrganizations.join(', ') : '—' }}
                                </p>
                            </FormRow>

                            <FormRow label="Publications by State:">
                                <p class="form-control-plaintext mb-0">{{ stateLabel || '—' }}</p>
                            </FormRow>

                            <FormRow label="Descriptions:">
                                <div v-html="values.description"></div>
                            </FormRow>

                            <FormRow label="Publication File:">
                                <a v-if="fileUrl" :href="fileUrl" target="_blank" rel="noopener">{{ fileUrl }}</a>
                                <span v-else>—</span>
                            </FormRow>

                            <FormRow label="Cover Image:">
                                <img v-if="imageUrl" :src="imageUrl" alt="" width="128" height="128">
                                <span v-else>—</span>
                            </FormRow>

                            <FormRow label="Publication Url:">
                                <a v-if="values.publication_url" :href="values.publication_url" target="_blank" rel="noopener">
                                    {{ values.publication_url }}
                                </a>
                                <span v-else>—</span>
                            </FormRow>
                        </div>

                        <div class="card-footer text-center">
                            <a :href="editUrl" class="btn btn-primary">Edit</a>
                            <a :href="indexUrl" class="btn btn-secondary ms-2">Back to publications</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
