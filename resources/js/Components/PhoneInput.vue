<script setup>
import { computed } from 'vue';
import { digitsOnly, formatPhoneNumber } from '@/lib/phone';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Phone',
    },
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    id: {
        type: String,
        default: undefined,
    },
});

const emit = defineEmits(['update:modelValue']);

// The model holds bare digits (what the server validates and stores); the input
// only ever shows the mask.
const display = computed(() => formatPhoneNumber(props.modelValue));

function onInput(event) {
    const digits = digitsOnly(event.target.value);

    // Re-apply the mask straight away so rejected characters cannot linger in
    // the DOM when `digits` (and therefore `display`) did not change.
    event.target.value = formatPhoneNumber(digits);

    emit('update:modelValue', digits);
}
</script>

<template>
    <input
        :id="id"
        type="tel"
        class="form-control"
        :value="display"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        @input="onInput"
    >
</template>
