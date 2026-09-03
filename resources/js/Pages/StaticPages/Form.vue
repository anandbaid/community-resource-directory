<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

import CkeditorField from '@/Components/Admin/CkeditorField.vue';
import FieldError from '@/Components/FieldError.vue';
import PageBuilder from '@/Components/Admin/PageBuilder.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

const props = defineProps({
    mode: { type: String, default: 'create' },
    submitUrl: { type: String, required: true },
    indexUrl: { type: String, required: true },
    assetUploadUrl: { type: String, required: true },
    ckeditorUrl: { type: String, default: '' },
    menuParents: { type: Array, default: () => [] },
    // Legacy pages have hand-written Blade templates keyed on their slug, so
    // their routing fields and the page builder are off limits.
    isLegacy: { type: Boolean, default: false },
    values: { type: Object, required: true },
    items: { type: Array, default: () => [] },
    pageContent: { type: Object, default: () => ({ html: '', css: '' }) },
});

const isEdit = computed(() => props.mode === 'edit');
const heading = computed(() => (isEdit.value ? 'Edit Static Page' : 'Add Static Page'));

const builder = ref(null);
const rows = ref(props.items.map((item) => ({ ...item })));

const form = useForm({
    ...(isEdit.value ? { _method: 'put' } : {}),
    ...props.values,
});

function addRow() {
    rows.value.push({
        id: '',
        title: '',
        sub_title: '',
        description: '',
        link: '',
        order: 0,
        image: null,
        image_existing: '',
        image_url: '',
        delete: 0,
    });
}

/**
 * Saved rows are flagged rather than dropped, because the server has to delete
 * the record and its uploaded image; unsaved ones can just disappear.
 */
function removeRow(index) {
    if (rows.value[index].id) {
        rows.value[index].delete = 1;
    } else {
        rows.value.splice(index, 1);
    }
}

const visibleRows = computed(() => rows.value.filter((row) => !row.delete));

function onRowImage(row, event) {
    row.image = event.target.files[0] ?? null;
}

function submit() {
    form
        .transform((data) => {
            const payload = { ...data };

            if (props.isLegacy) {
                rows.value.forEach((row, index) => {
                    payload[`items[${index}][id]`] = row.id ?? '';
                    payload[`items[${index}][delete]`] = String(row.delete ?? 0);
                    payload[`items[${index}][title]`] = row.title ?? '';
                    payload[`items[${index}][sub_title]`] = row.sub_title ?? '';
                    payload[`items[${index}][description]`] = row.description ?? '';
                    payload[`items[${index}][link]`] = row.link ?? '';
                    payload[`items[${index}][order]`] = row.order ?? 0;
                    payload[`items[${index}][image_existing]`] = row.image_existing ?? '';

                    if (row.image) {
                        payload[`items[${index}][image]`] = row.image;
                    }
                });

                return payload;
            }

            // The builder is only read here, at submit time.
            return { ...payload, ...(builder.value?.data() ?? {}) };
        })
        .post(props.submitUrl, { forceFormData: true, preserveScroll: true });
}
</script>

<template>
    <PageHeader
        :title="heading"
        :breadcrumbs="[{ label: 'Home' }, { label: 'Static Pages', url: indexUrl }, { label: heading }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ heading }}</h5>
                        </div>

                        <form class="px-3" @submit.prevent="submit">
                            <div class="card-body">
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2">
                                        <label class="form-label" for="title">Page Name</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input id="title" v-model="form.title" type="text" class="form-control" required>
                                        <FieldError :message="form.errors.title" />
                                    </div>
                                </div>

                                <template v-if="!isLegacy">
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label" for="status">Status</label>
                                        </div>
                                        <div class="col-md-10">
                                            <select id="status" v-model="form.status" class="form-select" required>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                            <FieldError :message="form.errors.status" />
                                        </div>
                                    </div>

                                    <div class="form-group row mb-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Header Menu</label>
                                        </div>
                                        <div class="col-md-10">
                                            <div class="form-check">
                                                <input
                                                    id="show_in_header"
                                                    v-model="form.show_in_header"
                                                    class="form-check-input"
                                                    type="checkbox"
                                                >
                                                <label class="form-check-label" for="show_in_header">
                                                    Show in header menu
                                                </label>
                                            </div>

                                            <select v-model="form.header_parent" class="form-select mt-2">
                                                <option value="">No parent (top level)</option>
                                                <option
                                                    v-for="parent in menuParents"
                                                    :key="parent.value"
                                                    :value="parent.value"
                                                >{{ parent.label }}</option>
                                            </select>

                                            <textarea
                                                v-model="form.header_menu_description"
                                                class="form-control mt-2"
                                                rows="2"
                                                maxlength="140"
                                                placeholder="Short description shown in the mega menu (optional)"
                                            ></textarea>
                                            <FieldError :message="form.errors.header_menu_description" />

                                            <input
                                                v-model="form.header_order"
                                                type="number"
                                                min="0"
                                                class="form-control mt-2"
                                                placeholder="Header order (optional)"
                                            >
                                            <FieldError :message="form.errors.header_order" />
                                        </div>
                                    </div>

                                    <div class="form-group row mb-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Footer Menu</label>
                                        </div>
                                        <div class="col-md-10">
                                            <div class="form-check">
                                                <input
                                                    id="show_in_footer"
                                                    v-model="form.show_in_footer"
                                                    class="form-check-input"
                                                    type="checkbox"
                                                >
                                                <label class="form-check-label" for="show_in_footer">
                                                    Show in footer menu
                                                </label>
                                            </div>
                                            <input
                                                v-model="form.footer_order"
                                                type="number"
                                                min="0"
                                                class="form-control mt-2"
                                                placeholder="Footer order (optional)"
                                            >
                                            <FieldError :message="form.errors.footer_order" />
                                        </div>
                                    </div>

                                    <div class="form-group row mb-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Description</label>
                                        </div>
                                        <div class="col-md-12">
                                            <PageBuilder
                                                ref="builder"
                                                :upload-url="assetUploadUrl"
                                                :html="pageContent.html"
                                                :css="pageContent.css"
                                            />
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <div
                                        v-for="field in ['description', 'content_1', 'content_2', 'content_3', 'content_4']"
                                        :key="field"
                                        class="form-group row mb-3"
                                    >
                                        <div class="col-md-2">
                                            <label class="form-label">
                                                {{ field === 'description' ? 'Description' : `Content ${field.slice(-1)}` }}
                                            </label>
                                        </div>
                                        <div class="col-md-10">
                                            <CkeditorField
                                                v-model="form[field]"
                                                :ckeditor-url="ckeditorUrl"
                                            />
                                            <FieldError :message="form.errors[field]" />
                                        </div>
                                    </div>

                                    <div class="form-group row mb-3">
                                        <div class="col-md-12 d-flex justify-content-between align-items-center">
                                            <h6><u>Page Items</u></h6>
                                            <button type="button" class="btn btn-success" @click="addRow">Add Item</button>
                                        </div>
                                    </div>

                                    <div v-for="(row, index) in rows" v-show="!row.delete" :key="index" class="card mb-2">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-end">
                                                <button type="button" class="btn btn-sm btn-danger" @click="removeRow(index)">
                                                    Remove
                                                </button>
                                            </div>
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label">Title</label>
                                                    <input v-model="row.title" type="text" class="form-control">
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label">Sub Title</label>
                                                    <input v-model="row.sub_title" type="text" class="form-control">
                                                </div>
                                                <div class="mb-3 col-md-12">
                                                    <label class="form-label">Description</label>
                                                    <CkeditorField v-model="row.description" :ckeditor-url="ckeditorUrl" :rows="3" />
                                                </div>
                                                <div class="mb-3 col-md-3">
                                                    <label class="form-label">Order</label>
                                                    <input v-model="row.order" type="number" min="0" class="form-control">
                                                </div>
                                                <div class="mb-3 col-md-5">
                                                    <label class="form-label">Link</label>
                                                    <input v-model="row.link" type="text" class="form-control">
                                                </div>
                                                <div class="mb-3 col-md-4">
                                                    <label class="form-label">Image</label>
                                                    <input type="file" class="form-control" @change="onRowImage(row, $event)">
                                                    <div v-if="row.image_url" class="mt-2">
                                                        <img :src="row.image_url" alt="" width="80" height="80">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <p v-if="!visibleRows.length" class="text-muted">No page items yet.</p>
                                </template>
                            </div>

                            <div class="card-footer d-flex">
                                <button type="submit" class="btn btn-primary save-btn" :disabled="form.processing">
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
