<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { loadScript } from '@/lib/loadScript';

const props = defineProps({
    modelValue: { type: String, default: '' },
    ckeditorUrl: { type: String, required: true },
    rows: { type: Number, default: 10 },
});

const emit = defineEmits(['update:modelValue']);

const textarea = ref(null);
let editor = null;
// Guards the echo when we push a new model value back into the editor.
let syncing = false;

onMounted(async () => {
    await loadScript(props.ckeditorUrl);

    if (!window.ClassicEditor || !textarea.value) {
        return;
    }

    // ckeditorConfig is published on window by public/assets/js/form-handle.js,
    // so Vue-mounted editors keep the same toolbar as the Blade ones.
    editor = await window.ClassicEditor.create(textarea.value, window.ckeditorConfig || {});
    editor.setData(props.modelValue ?? '');

    editor.model.document.on('change:data', () => {
        if (syncing) {
            return;
        }

        emit('update:modelValue', editor.getData());
    });
});

watch(
    () => props.modelValue,
    (value) => {
        if (!editor || editor.getData() === value) {
            return;
        }

        syncing = true;
        editor.setData(value ?? '');
        syncing = false;
    },
);

onBeforeUnmount(() => {
    editor?.destroy();
    editor = null;
});
</script>

<template>
    <textarea ref="textarea" class="form-control" cols="30" :rows="rows"></textarea>
</template>
