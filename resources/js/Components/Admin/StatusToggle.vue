<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    url: { type: String, required: true },
    status: { type: String, required: true },
    // What the row is called in the confirmation copy, e.g. "the user".
    subject: { type: String, default: 'the record' },
});

const busy = ref(false);

const next = computed(() => (props.status === 'active' ? 'inactive' : 'active'));

async function toggle() {
    const result = await window.Swal.fire({
        title: `Are you sure you want to ${next.value} ${props.subject}`,
        icon: 'warning',
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
    });

    if (!result.isConfirmed) {
        return;
    }

    busy.value = true;

    router.patch(props.url, { status: next.value }, {
        preserveScroll: true,
        onFinish: () => {
            busy.value = false;
        },
    });
}
</script>

<template>
    <button
        type="button"
        class="btn"
        :class="status === 'active' ? 'btn-secondary' : 'btn-warning'"
        :title="status === 'active' ? 'Inactive' : 'Active'"
        :disabled="busy"
        @click="toggle"
    >
        <i class="fas fa-lightbulb icon-spacer"></i>
    </button>
</template>
