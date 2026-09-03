<script setup>
import FieldError from '@/Components/FieldError.vue';

defineProps({
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    // Bootstrap column width for the control side.
    controlClass: { type: String, default: 'col-md-8' },
});
</script>

<template>
    <div class="form-group row mb-3 align-center">
        <div class="col-md-3">
            <label class="form-label">{{ label }}</label>
        </div>
        <div :class="controlClass">
            <slot />
            <FieldError :message="error" />
        </div>
    </div>
</template>
