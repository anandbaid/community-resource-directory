<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    url: { type: String, required: true },
    title: { type: String, default: 'Are you sure?' },
    text: { type: String, default: 'This action could not be reverted' },
    label: { type: String, default: '' },
});

const busy = ref(false);

async function confirmDelete() {
    const result = await window.Swal.fire({
        title: props.title,
        text: props.text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, do it!',
        cancelButtonText: 'No, cancel!',
        reverseButtons: true,
    });

    if (!result.isConfirmed) {
        return;
    }

    busy.value = true;

    router.delete(props.url, {
        preserveScroll: true,
        onFinish: () => {
            busy.value = false;
        },
    });
}
</script>

<template>
    <button type="button" class="btn btn-danger" :disabled="busy" @click="confirmDelete">
        <i class="fas fa-trash"></i>
        <span v-if="label" class="ms-1">{{ label }}</span>
    </button>
</template>
