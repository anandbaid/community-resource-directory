<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    // Array of publication rows on the parent form object.
    modelValue: { type: Array, required: true },
    publicationStates: { type: Array, default: () => [] },
    // Titles the server reported as already taken (lowercased).
    duplicateTitles: { type: Array, default: () => [] },
    errorPrefix: { type: String, default: 'publication' },
    errors: { type: Object, default: () => ({}) },
    /**
     * The public suggestion form and the admin review screen edit the same rows
     * on two different skins — labelled fields and Bootstrap buttons in the
     * admin, placeholders and the site's own button styles on the front end.
     */
    variant: { type: String, default: 'public', validator: (v) => ['public', 'admin'].includes(v) },
    readonly: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const fileInputs = ref({});

const isAdmin = computed(() => props.variant === 'admin');
const uploadClass = computed(() => (isAdmin.value ? 'btn btn-sm btn-primary' : 'cover-file-btn'));

function blankRow() {
    return {
        publication_title: '',
        publication_description: '',
        publication_state: '',
        publication_update_existing: 0,
        cover_file: null,
        cover_image: null,
        cover_file_path: '',
        cover_image_path: '',
        cover_file_name: '',
        cover_image_name: '',
    };
}

function addRow() {
    emit('update:modelValue', [...props.modelValue, blankRow()]);
}

function removeRow(index) {
    emit('update:modelValue', props.modelValue.filter((_, i) => i !== index));
}

function onFile(row, field, event) {
    const file = event.target.files[0] ?? null;

    row[field] = file;
    row[`${field}_name`] = file ? file.name : '';
}

function isDuplicate(row) {
    return props.duplicateTitles.includes(String(row.publication_title || '').trim().toLowerCase());
}

/** What to show under a file picker: the new pick, or what was already carried over. */
function fileLabel(row, field) {
    return row[`${field}_name`] || row[`${field}_path`] || '';
}

defineExpose({ addRow });
</script>

<template>
    <div>
        <div
            v-if="isAdmin"
            class="col-md-12 d-flex justify-content-between align-items-center"
        >
            <h6><u>Publications</u></h6>
            <button v-if="!readonly" type="button" class="btn btn-success mt-3" @click="addRow">
                Add More
            </button>
        </div>

        <div v-for="(row, index) in modelValue" :key="index" class="publication-item">
            <hr class="hr-line">

            <div class="mb-3 col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <label v-if="isAdmin" class="form-label">Title</label>
                    <button
                        v-if="!readonly"
                        type="button"
                        :class="isAdmin ? 'btn btn-danger' : 'remove-ico'"
                        @click="removeRow(index)"
                    >
                        <span v-if="isAdmin">Remove</span>
                        <i v-else class="fa-solid fa-trash"></i>
                    </button>
                </div>
                <input
                    v-model="row.publication_title"
                    type="text"
                    class="form-control publication-title"
                    placeholder="Title"
                >

                <!--
                    A title that already exists can only be submitted as an update
                    to that publication; the server blocks it otherwise.
                -->
                <div v-if="isDuplicate(row)" class="mt-2">
                    <div class="text-danger small mb-1">
                        A publication with this title already exists.
                    </div>
                    <label class="check-label">
                        <input
                            v-model="row.publication_update_existing"
                            type="checkbox"
                            :true-value="1"
                            :false-value="0"
                            class="custom-checkbox"
                        >
                        Suggest an update to the existing publication instead
                    </label>
                </div>
            </div>

            <div class="mb-3 col-md-12">
                <label v-if="isAdmin" class="form-label">Publications by State</label>
                <select v-model="row.publication_state" class="form-select publication-states">
                    <option value="">Select State</option>
                    <option value="national">National</option>
                    <option v-for="state in publicationStates" :key="state.id" :value="String(state.id)">
                        {{ state.name }}
                    </option>
                </select>
            </div>

            <div class="mb-3 col-md-12">
                <label v-if="isAdmin" class="form-label">Description</label>
                <div :class="isAdmin ? '' : 'textarea-wrapper'">
                    <textarea
                        v-model="row.publication_description"
                        cols="30"
                        rows="4"
                        maxlength="250"
                        :class="isAdmin ? 'form-control' : ''"
                        placeholder="Description"
                    ></textarea>
                    <span v-if="!isAdmin" class="textarea-note">(250-character limit)</span>
                </div>
            </div>

            <div class="form-group col-md-12 mb-3">
                <label class="form-label gap-3">{{ isAdmin ? 'Publication File:' : 'File URL:' }}
                    <div class="cover_file_container">
                        <input
                            :ref="(el) => (fileInputs[`file-${index}`] = el)"
                            type="file"
                            class="d-none"
                            @change="onFile(row, 'cover_file', $event)"
                        >
                        <button
                            v-if="!readonly"
                            type="button"
                            :class="uploadClass"
                            @click="fileInputs[`file-${index}`].click()"
                        >
                            <i v-if="isAdmin" class="fas fa-upload"></i>
                            {{ isAdmin ? ' Upload File' : 'Choose File' }}
                            <i v-if="!isAdmin" class="fas fa-upload"></i>
                        </button>
                        <div><span class="file-name-display">{{ fileLabel(row, 'cover_file') }}</span></div>
                    </div>
                </label>
                <div v-if="errors[`${errorPrefix}_file_${index}`]" class="invalid-feedback d-block">
                    {{ errors[`${errorPrefix}_file_${index}`] }}
                </div>
            </div>

            <div class="form-group col-md-12 mb-3">
                <label class="form-label gap-3">Cover Image:
                    <div class="cover_file_container">
                        <input
                            :ref="(el) => (fileInputs[`image-${index}`] = el)"
                            type="file"
                            class="d-none"
                            accept="image/*"
                            @change="onFile(row, 'cover_image', $event)"
                        >
                        <button
                            v-if="!readonly"
                            type="button"
                            :class="uploadClass"
                            @click="fileInputs[`image-${index}`].click()"
                        >
                            <i v-if="isAdmin" class="fas fa-upload"></i>
                            {{ isAdmin ? ' Upload Image' : 'Choose Image' }}
                            <i v-if="!isAdmin" class="fas fa-upload"></i>
                        </button>
                        <div><span class="file-name-display">{{ fileLabel(row, 'cover_image') }}</span></div>
                    </div>
                </label>
            </div>
        </div>

        <div v-if="!isAdmin" class="mt-3">
            <button type="button" class="secondary-btn" @click="addRow">Add Publication</button>
        </div>
    </div>
</template>
