<script setup>
import { ref } from 'vue';

defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Password*' },
    autocomplete: { type: String, default: 'current-password' },
});

defineEmits(['update:modelValue']);

const visible = ref(false);
</script>

<template>
    <div class="password-eye">
        <input
            :value="modelValue"
            :type="visible ? 'text' : 'password'"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            class="custom-password"
            @input="$emit('update:modelValue', $event.target.value)"
        >
        <!--
            The Blade pages bound this to a jQuery handler in script.js that keys
            off `.password-eye-icon`; owning the state here keeps the icon and the
            input type from drifting apart.
        -->
        <i
            class="fa-solid password-eye-icon"
            :class="visible ? 'fa-eye-slash' : 'fa-eye'"
            role="button"
            tabindex="0"
            :aria-label="visible ? 'Hide password' : 'Show password'"
            :aria-pressed="visible"
            @click="visible = !visible"
            @keydown.enter.prevent="visible = !visible"
            @keydown.space.prevent="visible = !visible"
        ></i>
    </div>
</template>
