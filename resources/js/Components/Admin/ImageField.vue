<script setup>
import { ref } from 'vue';

const props = defineProps({
    label: { type: String, default: '' },
    // Currently stored image, if any.
    currentUrl: { type: String, default: '' },
    error: { type: String, default: '' },
    height: { type: Number, default: 80 },
});

const emit = defineEmits(['change']);

const preview = ref(props.currentUrl);

function onChange(event) {
    const file = event.target.files[0] ?? null;

    preview.value = file ? URL.createObjectURL(file) : props.currentUrl;
    emit('change', file);
}
</script>

<template>
    <div class="mb-3">
        <label v-if="label" class="form-label">{{ label }}</label>
        <input type="file" class="form-control" accept="image/*" @change="onChange">

        <div v-if="preview" class="mt-2">
            <img :src="preview" alt="" :height="height">
        </div>

        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>
