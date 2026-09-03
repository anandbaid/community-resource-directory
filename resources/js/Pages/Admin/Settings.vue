<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

import CkeditorField from '@/Components/Admin/CkeditorField.vue';
import FieldError from '@/Components/FieldError.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import RepeatableInputs from '@/Components/Admin/RepeatableInputs.vue';

const props = defineProps({
    submitUrl: { type: String, required: true },
    clearCacheUrl: { type: String, required: true },
    ckeditorUrl: { type: String, default: '' },
    values: { type: Object, required: true },
    lists: { type: Object, required: true },
    logos: { type: Object, default: () => ({ header: '', footer: '' }) },
});

const form = useForm({
    ...props.values,
    footer_hq_phone: [...props.lists.footer_hq_phone],
    footer_hq_email: [...props.lists.footer_hq_email],
    footer_mailing_phone: [...props.lists.footer_mailing_phone],
    footer_mailing_email: [...props.lists.footer_mailing_email],
    header_logo: null,
    footer_logo: null,
});

const clearingCache = ref(false);

const SCALARS = [
    'website_name', 'site_title', 'meta_title', 'meta_keywords', 'meta_description',
    'copy_right', 'footer_description', 'footer_hq_address', 'footer_hq_phone_hours',
    'footer_mailing_address', 'footer_mailing_phone_hours', 'admin_email',
];

const LISTS = ['footer_hq_phone', 'footer_hq_email', 'footer_mailing_phone', 'footer_mailing_email'];

function onLogo(field, event) {
    form[field] = event.target.files[0] ?? null;
}

/** Errors come back keyed `key.admin_email`, matching the posted shape. */
function settingError(name) {
    return form.errors[`key.${name}`];
}

function submit() {
    form
        .transform((data) => {
            const payload = {};

            SCALARS.forEach((name) => {
                payload[`key[${name}]`] = data[name] ?? '';
            });

            LISTS.forEach((name) => {
                (data[name] ?? []).forEach((value, index) => {
                    payload[`key[${name}][${index}]`] = value ?? '';
                });
            });

            if (data.header_logo) {
                payload.header_logo = data.header_logo;
            }
            if (data.footer_logo) {
                payload.footer_logo = data.footer_logo;
            }

            return payload;
        })
        .post(props.submitUrl, { forceFormData: true, preserveScroll: true });
}

function clearCache() {
    clearingCache.value = true;

    router.post(props.clearCacheUrl, {}, {
        preserveScroll: true,
        onFinish: () => {
            clearingCache.value = false;
        },
    });
}
</script>

<template>
    <PageHeader title="General Settings" :breadcrumbs="[{ label: 'Home' }, { label: 'General Settings' }]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                            <h5 class="card-title mb-0">General Settings</h5>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary"
                                :disabled="clearingCache"
                                @click="clearCache"
                            >
                                {{ clearingCache ? 'Clearing…' : 'Clear Frontend Cache' }}
                            </button>
                        </div>

                        <form class="px-3" @submit.prevent="submit">
                            <div class="card-body">
                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2"><label class="form-label">Website Name</label></div>
                                    <div class="col-md-10">
                                        <input v-model="form.website_name" type="text" class="form-control" placeholder="Enter ..." required>
                                        <FieldError :message="settingError('website_name')" />
                                    </div>
                                </div>

                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2"><label class="form-label">Site Title</label></div>
                                    <div class="col-md-10">
                                        <input v-model="form.site_title" type="text" class="form-control" placeholder="Enter ...">
                                        <FieldError :message="settingError('site_title')" />
                                    </div>
                                </div>

                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2"><label class="form-label">Meta Title</label></div>
                                    <div class="col-md-10">
                                        <input v-model="form.meta_title" type="text" class="form-control" placeholder="Enter ...">
                                        <FieldError :message="settingError('meta_title')" />
                                    </div>
                                </div>

                                <div class="form-group row mb-3">
                                    <div class="col-md-2"><label class="form-label">Meta Keywords</label></div>
                                    <div class="col-md-10">
                                        <textarea v-model="form.meta_keywords" class="form-control h-300" placeholder="Enter ..."></textarea>
                                        <FieldError :message="settingError('meta_keywords')" />
                                    </div>
                                </div>

                                <div class="form-group row mb-3">
                                    <div class="col-md-2"><label class="form-label">Meta Description</label></div>
                                    <div class="col-md-10">
                                        <textarea v-model="form.meta_description" class="form-control h-300" placeholder="Enter ..."></textarea>
                                        <FieldError :message="settingError('meta_description')" />
                                    </div>
                                </div>

                                <div class="form-group row mb-3">
                                    <div class="col-md-2"><label class="form-label">Copyright</label></div>
                                    <div class="col-md-10">
                                        <CkeditorField v-model="form.copy_right" :ckeditor-url="ckeditorUrl" :rows="4" />
                                    </div>
                                </div>

                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2"><label class="form-label">Header Logo</label></div>
                                    <div class="col-md-10">
                                        <input type="file" class="form-control" accept="image/*" @change="onLogo('header_logo', $event)">
                                        <div v-if="logos.header" class="mt-2">
                                            <img :src="logos.header" alt="Header logo" height="60">
                                        </div>
                                        <FieldError :message="form.errors.header_logo" />
                                    </div>
                                </div>

                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2"><label class="form-label">Footer Logo</label></div>
                                    <div class="col-md-10">
                                        <input type="file" class="form-control" accept="image/*" @change="onLogo('footer_logo', $event)">
                                        <div v-if="logos.footer" class="mt-2">
                                            <img :src="logos.footer" alt="Footer logo" height="60">
                                        </div>
                                        <FieldError :message="form.errors.footer_logo" />
                                    </div>
                                </div>

                                <div class="form-group row mb-3">
                                    <div class="col-md-2"><label class="form-label">Footer Description</label></div>
                                    <div class="col-md-10">
                                        <CkeditorField v-model="form.footer_description" :ckeditor-url="ckeditorUrl" :rows="4" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><u>Headquarters</u></h6>

                                        <div class="mb-3">
                                            <label class="form-label">HQ Address</label>
                                            <textarea v-model="form.footer_hq_address" class="form-control h-120" placeholder="Enter ..."></textarea>
                                        </div>

                                        <RepeatableInputs
                                            v-model="form.footer_hq_phone"
                                            label="HQ Phones"
                                            add-label="Add Phone"
                                        />

                                        <div class="mb-3">
                                            <label class="form-label">HQ Phone Hours (optional)</label>
                                            <input v-model="form.footer_hq_phone_hours" type="text" class="form-control" placeholder="Enter ...">
                                        </div>

                                        <RepeatableInputs
                                            v-model="form.footer_hq_email"
                                            label="HQ Emails"
                                            add-label="Add Email"
                                            type="email"
                                            :error="form.errors['key.footer_hq_email.0']"
                                        />
                                    </div>

                                    <div class="col-md-6">
                                        <h6><u>Mailing</u></h6>

                                        <div class="mb-3">
                                            <label class="form-label">Mailing Address</label>
                                            <textarea v-model="form.footer_mailing_address" class="form-control h-120" placeholder="Enter ..."></textarea>
                                        </div>

                                        <RepeatableInputs
                                            v-model="form.footer_mailing_phone"
                                            label="Mailing Phones"
                                            add-label="Add Phone"
                                        />

                                        <div class="mb-3">
                                            <label class="form-label">Mailing Phone Hours (optional)</label>
                                            <input v-model="form.footer_mailing_phone_hours" type="text" class="form-control" placeholder="Enter ...">
                                        </div>

                                        <RepeatableInputs
                                            v-model="form.footer_mailing_email"
                                            label="Mailing Emails"
                                            add-label="Add Email"
                                            type="email"
                                            :error="form.errors['key.footer_mailing_email.0']"
                                        />
                                    </div>
                                </div>

                                <div class="form-group row mb-3 align-center">
                                    <div class="col-md-2"><label class="form-label">Admin Email</label></div>
                                    <div class="col-md-10">
                                        <input v-model="form.admin_email" type="email" class="form-control" placeholder="Enter Admin Email Id" required>
                                        <FieldError :message="settingError('admin_email')" />
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary save-btn" :disabled="form.processing">
                                    {{ form.processing ? 'Saving…' : 'Save' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
