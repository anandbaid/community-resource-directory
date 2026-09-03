<script setup>
import { reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';

import CkeditorField from '@/Components/Admin/CkeditorField.vue';
import FieldError from '@/Components/FieldError.vue';
import ImageField from '@/Components/Admin/ImageField.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

const props = defineProps({
    submitUrl: { type: String, required: true },
    ckeditorUrl: { type: String, default: '' },
    // Every non-image setting, keyed by its settings_name.
    values: { type: Object, required: true },
    images: { type: Object, required: true },
    whatWeDoItems: { type: Array, default: () => [] },
    careerIcons: { type: Array, default: () => [] },
});

const form = useForm({ ...props.values });

// Uploads are held apart from the text fields, keyed the way the server reads
// them: `key[...]` for settings, a bare name for each file.
const uploads = reactive({});

function onImage(name, file) {
    uploads[name] = file;
}

function submit() {
    form
        .transform((data) => {
            const payload = {};

            Object.entries(data).forEach(([name, value]) => {
                payload[`key[${name}]`] = value ?? '';
            });

            Object.entries(uploads).forEach(([name, file]) => {
                if (file) {
                    payload[name] = file;
                }
            });

            return payload;
        })
        .post(props.submitUrl, { forceFormData: true, preserveScroll: true });
}
</script>

<template>
    <PageHeader title="Home Page Sections" :breadcrumbs="[{ label: 'Home' }, { label: 'Home Page Sections' }]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Home Page Sections</h5>
                        </div>

                        <form class="px-3" @submit.prevent="submit">
                            <div class="card-body">
                                <h6 class="mb-3"><b>Resource Directory</b></h6>
                                <div class="mb-3">
                                    <label class="form-label">Paragraph 1</label>
                                    <CkeditorField v-model="form.home_resource_block_1" :ckeditor-url="ckeditorUrl" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Paragraph 2</label>
                                    <CkeditorField v-model="form.home_resource_block_2" :ckeditor-url="ckeditorUrl" />
                                </div>

                                <hr>
                                <h6 class="mb-3"><b>What We Do</b></h6>
                                <div class="mb-3">
                                    <label class="form-label">Intro</label>
                                    <CkeditorField v-model="form.home_what_we_do_block" :ckeditor-url="ckeditorUrl" />
                                </div>

                                <!--
                                    Four cards with the same five fields. The
                                    Blade view spelled each one out in full.
                                -->
                                <div v-for="item in whatWeDoItems" :key="item.index" class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Item {{ item.index }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Title</label>
                                                <input v-model="form[item.title]" type="text" class="form-control">
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Link</label>
                                                <input v-model="form[item.link]" type="text" class="form-control">
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label">Description</label>
                                                <textarea v-model="form[item.desc]" class="form-control" rows="3"></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <ImageField
                                                    label="Image"
                                                    :current-url="images[item.image]"
                                                    :error="form.errors[item.image]"
                                                    @change="onImage(item.image, $event)"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <ImageField
                                                    label="Icon"
                                                    :current-url="images[item.icon]"
                                                    :error="form.errors[item.icon]"
                                                    @change="onImage(item.icon, $event)"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h6 class="mb-3"><b>Our Shop</b></h6>
                                <div class="mb-3">
                                    <CkeditorField v-model="form.home_shop_block" :ckeditor-url="ckeditorUrl" />
                                </div>

                                <hr>
                                <h6 class="mb-3"><b>About Section</b></h6>
                                <div class="mb-3">
                                    <CkeditorField v-model="form.home_about_block" :ckeditor-url="ckeditorUrl" />
                                </div>
                                <ImageField
                                    label="About Image"
                                    :current-url="images.home_about_image"
                                    :error="form.errors.home_about_image"
                                    @change="onImage('home_about_image', $event)"
                                />

                                <hr>
                                <h6 class="mb-3"><b>Career Success Hub</b></h6>
                                <div class="mb-3">
                                    <CkeditorField
                                        v-model="form.home_career_success_hub_block"
                                        :ckeditor-url="ckeditorUrl"
                                    />
                                </div>

                                <div class="row">
                                    <div v-for="icon in careerIcons" :key="icon.index" class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Icon {{ icon.index }} Title</label>
                                            <input v-model="form[icon.title]" type="text" class="form-control">
                                        </div>
                                        <ImageField
                                            :current-url="images[icon.image]"
                                            :error="form.errors[icon.image]"
                                            :height="60"
                                            @change="onImage(icon.image, $event)"
                                        />
                                    </div>
                                </div>

                                <ImageField
                                    label="Career Hub Image"
                                    :current-url="images.home_career_success_hub_image"
                                    :error="form.errors.home_career_success_hub_image"
                                    @change="onImage('home_career_success_hub_image', $event)"
                                />

                                <hr>
                                <h6 class="mb-3"><b>Support Section</b></h6>
                                <div class="mb-3">
                                    <CkeditorField v-model="form.home_support_block" :ckeditor-url="ckeditorUrl" />
                                    <FieldError :message="form.errors.home_support_block" />
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
