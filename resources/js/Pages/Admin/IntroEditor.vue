<script setup>
import { useForm } from '@inertiajs/vue3';

import CkeditorField from '@/Components/Admin/CkeditorField.vue';
import FieldError from '@/Components/FieldError.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

/**
 * One rich-text block behind a named setting.
 *
 * Serves both the Library Sections and Resources Sections screens, which were
 * two Blade views identical apart from the setting name and the headings.
 */
const props = defineProps({
    title: { type: String, required: true },
    blockLabel: { type: String, required: true },
    submitUrl: { type: String, required: true },
    ckeditorUrl: { type: String, default: '' },
    // The request field, which is also the settings row name.
    field: { type: String, required: true },
    content: { type: String, default: '' },
});

const form = useForm({ [props.field]: props.content });

function submit() {
    form.post(props.submitUrl, { preserveScroll: true });
}
</script>

<template>
    <PageHeader :title="title" :breadcrumbs="[{ label: 'Home' }, { label: title }]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ title }}</h5>
                        </div>

                        <form class="px-3" @submit.prevent="submit">
                            <div class="card-body">
                                <h6 class="mb-3"><b>{{ blockLabel }}</b></h6>
                                <div class="form-group row mb-3">
                                    <div class="col-md-2">
                                        <label class="form-label">Paragraph 1</label>
                                    </div>
                                    <div class="col-md-10">
                                        <CkeditorField v-model="form[field]" :ckeditor-url="ckeditorUrl" />
                                        <FieldError :message="form.errors[field]" />
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer d-flex">
                                <button type="submit" class="btn btn-primary save-btn" :disabled="form.processing">
                                    {{ form.processing ? 'Saving…' : 'Submit' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
