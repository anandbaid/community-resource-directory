<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { createPageBuilder, pageBuilderData } from '@/lib/pageBuilder';

const props = defineProps({
    uploadUrl: { type: String, required: true },
    html: { type: String, default: '' },
    css: { type: String, default: '' },
});

const container = ref(null);
const error = ref('');

let editor = null;

onMounted(async () => {
    try {
        editor = await createPageBuilder({
            container: container.value,
            uploadUrl: props.uploadUrl,
            html: props.html,
            css: props.css,
        });
    } catch (e) {
        error.value = e.message;
    }
});

onBeforeUnmount(() => {
    editor?.destroy();
    editor = null;
});

// Read at submit time rather than mirrored into reactive state: GrapesJS fires
// change events constantly and the form only needs the final document.
defineExpose({
    data: () => pageBuilderData(editor),
    ready: () => editor !== null,
});
</script>

<template>
    <div class="gjs-editor-wrapper">
        <div ref="container"></div>
        <p v-if="error" class="text-danger mt-2 mb-0">{{ error }}</p>
    </div>
</template>
