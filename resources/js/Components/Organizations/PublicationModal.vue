<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { swalAlert } from '@/lib/flash';
import { loadScript } from '@/lib/loadScript';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    // [{ id, name }] — the publication endpoint keys state by id, unlike the
    // organization address selects which key by name.
    states: {
        type: Array,
        default: () => [],
    },
    storeUrl: {
        type: String,
        required: true,
    },
    placeholderImage: {
        type: String,
        default: '',
    },
    ckeditorUrl: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close', 'created']);

const modalEl = ref(null);
const descriptionEl = ref(null);
const coverImageInput = ref(null);
const publicationFileInput = ref(null);

let modal = null;
let editor = null;

const saving = ref(false);
const errors = ref({});
const form = ref(blankForm());
const coverImagePreview = ref(props.placeholderImage);
const publicationFileName = ref('');

function blankForm() {
    return {
        title: '',
        state: '',
        description: '',
        publication_url: '',
        cover_image: null,
        publication_file: null,
    };
}

function reset() {
    form.value = blankForm();
    errors.value = {};
    coverImagePreview.value = props.placeholderImage;
    publicationFileName.value = '';

    if (coverImageInput.value) {
        coverImageInput.value.value = '';
    }
    if (publicationFileInput.value) {
        publicationFileInput.value.value = '';
    }
    if (editor) {
        editor.setData('');
    }
}

async function mountEditor() {
    if (editor || !descriptionEl.value || !props.ckeditorUrl) {
        return;
    }

    await loadScript(props.ckeditorUrl);

    if (!window.ClassicEditor || editor) {
        return;
    }

    // ckeditorConfig is published on window by public/assets/js/form-handle.js
    // so the modal keeps the same toolbar as the Blade editors.
    editor = await window.ClassicEditor.create(descriptionEl.value, window.ckeditorConfig || {});
    editor.model.document.on('change:data', () => {
        form.value.description = editor.getData();
    });
}

watch(
    () => props.open,
    async (open) => {
        await nextTick();

        if (open) {
            reset();
            await mountEditor();

            if (!modal && window.bootstrap && modalEl.value) {
                modal = new window.bootstrap.Modal(modalEl.value);
                modalEl.value.addEventListener('hidden.bs.modal', () => emit('close'));
            }

            modal?.show();
        } else {
            modal?.hide();
        }
    },
);

onBeforeUnmount(() => {
    modal?.dispose();
    editor?.destroy();
});

function onCoverImageChange(event) {
    const file = event.target.files[0] ?? null;

    form.value.cover_image = file;
    coverImagePreview.value = file ? URL.createObjectURL(file) : props.placeholderImage;
}

function onPublicationFileChange(event) {
    const file = event.target.files[0] ?? null;

    form.value.publication_file = file;
    publicationFileName.value = file ? file.name : '';
}

/**
 * The endpoint answers XHR callers with either a legacy `errors` string or —
 * since it moved to $request->validate() — Laravel's field-keyed error bag.
 */
function extractErrors(error) {
    const payload = error.response?.data;

    if (!payload) {
        return 'An unexpected error occurred.';
    }

    if (typeof payload.errors === 'string') {
        return payload.errors;
    }

    if (payload.errors && typeof payload.errors === 'object') {
        return Object.values(payload.errors).flat().join('\n');
    }

    return payload.message ?? 'An unexpected error occurred.';
}

async function save() {
    errors.value = {};

    if (!form.value.cover_image) {
        errors.value.cover_image = 'This field is required';
    }
    if (!form.value.publication_file) {
        errors.value.publication_file = 'This field is required';
    }
    if (!form.value.title) {
        errors.value.title = 'This field is required';
    }
    if (!form.value.state) {
        errors.value.state = 'This field is required';
    }
    if (!form.value.description) {
        errors.value.description = 'This field is required';
    }
    if (Object.keys(errors.value).length > 0) {
        return;
    }

    const data = new FormData();

    Object.entries(form.value).forEach(([key, value]) => {
        if (value !== null && value !== '') {
            data.append(key, value);
        }
    });

    saving.value = true;

    try {
        const response = await window.axios.post(props.storeUrl, data, {
            headers: { 'X-CSRF-TOKEN': window.csrf_token },
        });

        emit('created', response.data.data);
        emit('close');
    } catch (error) {
        swalAlert('error', extractErrors(error), 4000);
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Teleport to="body">
        <div
            ref="modalEl"
            class="modal fade add-new-publication"
            tabindex="-1"
            aria-labelledby="create-new-publication-label"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="create-new-publication-label" class="modal-title">New Publication</h5>
                        <button type="button" class="btn-close" aria-label="Close" @click="emit('close')"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row mb-3 align-center">
                            <div class="col-md-3">
                                <label class="form-label">Title: </label>
                            </div>
                            <div class="col-md-8">
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="form-control"
                                    placeholder="Enter a Publication title"
                                    required
                                >
                                <div v-if="errors.title" class="invalid-feedback d-block">{{ errors.title }}</div>
                            </div>
                        </div>

                        <div class="form-group row mb-3 align-center">
                            <div class="col-md-3">
                                <label class="form-label">Publications by State: </label>
                            </div>
                            <div class="col-md-3">
                                <select v-model="form.state" class="form-select" required>
                                    <option value="">Select State</option>
                                    <option value="national">National</option>
                                    <option v-for="state in states" :key="state.id" :value="state.id">
                                        {{ state.name }}
                                    </option>
                                </select>
                                <div v-if="errors.state" class="invalid-feedback d-block">{{ errors.state }}</div>
                            </div>
                        </div>

                        <div class="form-group row mb-3 align-center">
                            <div class="col-md-3">
                                <label class="form-label">Descriptions: </label>
                            </div>
                            <div class="col-md-8">
                                <textarea ref="descriptionEl" class="form-control" cols="30" rows="10"></textarea>
                                <div v-if="errors.description" class="invalid-feedback d-block">
                                    {{ errors.description }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-3 align-center">
                            <div class="col-md-3">
                                <label class="form-label">Publication File: </label>
                            </div>
                            <div class="col-md-8">
                                <div class="mt-2">
                                    <input
                                        ref="publicationFileInput"
                                        type="file"
                                        class="d-none"
                                        @change="onPublicationFileChange"
                                    >
                                    <button
                                        type="button"
                                        class="btn btn-primary"
                                        @click="publicationFileInput.click()"
                                    >
                                        <i class="fas fa-upload"></i> Upload File
                                    </button>
                                </div>
                                <span>{{ publicationFileName }}</span>
                                <div v-if="errors.publication_file" class="invalid-feedback d-block">
                                    {{ errors.publication_file }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-3 align-center">
                            <div class="col-md-3">
                                <label class="form-label">Cover Image: </label>
                            </div>
                            <div class="col-md-8">
                                <img
                                    :src="coverImagePreview"
                                    alt=""
                                    class="img-responsive mt-2"
                                    width="128"
                                    height="128"
                                >
                                <div class="mt-2">
                                    <input
                                        ref="coverImageInput"
                                        type="file"
                                        class="d-none"
                                        accept="image/*"
                                        @change="onCoverImageChange"
                                    >
                                    <button type="button" class="btn btn-primary" @click="coverImageInput.click()">
                                        <i class="fas fa-upload"></i> Upload
                                    </button>
                                </div>
                                <div v-if="errors.cover_image" class="invalid-feedback d-block">
                                    {{ errors.cover_image }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-3 align-center">
                            <div class="col-md-3">
                                <label class="form-label">Publication Url: </label>
                            </div>
                            <div class="col-md-8">
                                <input v-model="form.publication_url" type="url" class="form-control">
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-primary" :disabled="saving" @click="save">
                                {{ saving ? 'Saving…' : 'Save' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
