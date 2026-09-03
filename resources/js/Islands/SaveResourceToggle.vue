<script setup>
import { ref } from 'vue';
import { swalAlert } from '@/lib/flash';

const props = defineProps({
    url: { type: String, required: true },
    saved: { type: Boolean, default: false },
});

const isSaved = ref(props.saved);
const busy = ref(false);

async function toggle() {
    if (busy.value) {
        return;
    }

    busy.value = true;

    // The endpoint keys off the *previous* state: 'exists' removes, anything
    // else adds.
    const previous = isSaved.value;

    try {
        await window.axios.get(props.url, {
            params: { saved: previous ? 'exists' : 'not-exists' },
        });

        isSaved.value = !previous;
    } catch (error) {
        swalAlert('error', error.response?.data?.errors ?? 'Could not update your saved resources.', 4000);
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <span
        class="ico sm-ico saved-resource"
        :class="{ active: isSaved }"
        role="button"
        tabindex="0"
        :aria-pressed="isSaved"
        :aria-label="isSaved ? 'Remove from saved resources' : 'Save this resource'"
        @click="toggle"
        @keydown.enter.prevent="toggle"
        @keydown.space.prevent="toggle"
    >
        <i class="fa-regular fa-heart"></i>
    </span>
</template>
