<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

import CkeditorField from '@/Components/Admin/CkeditorField.vue';
import FieldError from '@/Components/FieldError.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';

const props = defineProps({
    indexUrl: { type: String, required: true },
    submitUrl: { type: String, required: true },
    ckeditorUrl: { type: String, default: '' },
    values: { type: Object, required: true },
    // Four fixed segments, each owning an ordered list of topics.
    segments: { type: Array, default: () => [] },
});

const form = useForm({
    _method: 'put',
    title: props.values.title ?? '',
    description: props.values.description ?? '',
});

const segments = ref(props.segments.map((segment) => ({
    ...segment,
    image: null,
    topics: segment.topics.map((topic) => ({ ...topic })),
})));

function addTopic(segment) {
    segment.topics.push({
        id: '',
        title: '',
        description: '',
        order: segment.topics.length,
        image: null,
        image_existing: '',
        image_url: '',
        delete: 0,
    });
}

/**
 * A saved topic is flagged so the server can delete the row and its image; an
 * unsaved one can simply go.
 */
function removeTopic(segment, index) {
    if (segment.topics[index].id) {
        segment.topics[index].delete = 1;
    } else {
        segment.topics.splice(index, 1);
    }
}

function onSegmentImage(segment, event) {
    segment.image = event.target.files[0] ?? null;
}

function onTopicImage(topic, event) {
    topic.image = event.target.files[0] ?? null;
}

function submit() {
    form
        .transform((data) => {
            const payload = { ...data };

            segments.value.forEach((segment) => {
                const s = segment.index;

                payload[`segment_items[${s}][text]`] = segment.text ?? '';
                payload[`segment_items[${s}][description]`] = segment.description ?? '';

                if (segment.image) {
                    payload[`segment_items[${s}][image]`] = segment.image;
                }

                segment.topics.forEach((topic, index) => {
                    const key = `topic_items[${s}][${index}]`;

                    payload[`${key}[id]`] = topic.id ?? '';
                    payload[`${key}[delete]`] = String(topic.delete ?? 0);
                    payload[`${key}[title]`] = topic.title ?? '';
                    payload[`${key}[description]`] = topic.description ?? '';
                    payload[`${key}[order]`] = topic.order ?? 0;
                    payload[`${key}[image_existing]`] = topic.image_existing ?? '';

                    if (topic.image) {
                        payload[`${key}[image]`] = topic.image;
                    }
                });
            });

            return payload;
        })
        .post(props.submitUrl, { forceFormData: true, preserveScroll: true });
}
</script>

<template>
    <PageHeader
        title="Career Success Hub"
        :breadcrumbs="[
            { label: 'Home' },
            { label: 'Static Pages', url: indexUrl },
            { label: 'Career Success Hub' },
        ]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Career Success Hub</h5>
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

                                <div class="form-group row mb-3">
                                    <div class="col-md-2">
                                        <label class="form-label">Description</label>
                                    </div>
                                    <div class="col-md-10">
                                        <CkeditorField v-model="form.description" :ckeditor-url="ckeditorUrl" />
                                        <FieldError :message="form.errors.description" />
                                    </div>
                                </div>

                                <FieldError :message="form.errors.segment_items" />
                                <FieldError :message="form.errors.topic_items" />

                                <div v-for="segment in segments" :key="segment.index" class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Segment {{ segment.index }}</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="mb-3 col-md-8">
                                                <label class="form-label">Segment Text</label>
                                                <input v-model="segment.text" type="text" class="form-control" required>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <label class="form-label">Segment Image</label>
                                                <input type="file" accept="image/*" class="form-control" @change="onSegmentImage(segment, $event)">
                                                <div v-if="segment.imageUrl" class="mt-2">
                                                    <img :src="segment.imageUrl" alt="" width="90" height="90">
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label">Segment Description</label>
                                                <CkeditorField
                                                    v-model="segment.description"
                                                    :ckeditor-url="ckeditorUrl"
                                                    :rows="3"
                                                />
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><u>Topics</u></h6>
                                            <button type="button" class="btn btn-sm btn-primary" @click="addTopic(segment)">
                                                Add Topic
                                            </button>
                                        </div>

                                        <div
                                            v-for="(topic, index) in segment.topics"
                                            v-show="!topic.delete"
                                            :key="index"
                                            class="card mb-2 mt-2"
                                        >
                                            <div class="card-body">
                                                <div class="d-flex justify-content-end">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-danger"
                                                        @click="removeTopic(segment, index)"
                                                    >Remove</button>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-8">
                                                        <label class="form-label">Title</label>
                                                        <input v-model="topic.title" type="text" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3 col-md-4">
                                                        <label class="form-label">Order</label>
                                                        <input v-model="topic.order" type="number" min="0" class="form-control">
                                                    </div>
                                                    <div class="mb-3 col-md-12">
                                                        <label class="form-label">Description</label>
                                                        <CkeditorField
                                                            v-model="topic.description"
                                                            :ckeditor-url="ckeditorUrl"
                                                            :rows="3"
                                                        />
                                                    </div>
                                                    <div class="mb-3 col-md-4">
                                                        <label class="form-label">Image</label>
                                                        <input type="file" accept="image/*" class="form-control" @change="onTopicImage(topic, $event)">
                                                        <div v-if="topic.image_url" class="mt-2">
                                                            <img :src="topic.image_url" alt="" width="80" height="80">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
