<script setup>
const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    label: { type: String, default: '' },
    addLabel: { type: String, default: 'Add' },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: 'Enter ...' },
    error: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

function update(index, value) {
    emit('update:modelValue', props.modelValue.map((item, i) => (i === index ? value : item)));
}

function add() {
    emit('update:modelValue', [...props.modelValue, '']);
}

function remove(index) {
    const next = props.modelValue.filter((_, i) => i !== index);

    // Keep one row so the field never disappears entirely.
    emit('update:modelValue', next.length ? next : ['']);
}
</script>

<template>
    <div class="mb-3">
        <label v-if="label" class="form-label">{{ label }}</label>

        <!--
            The Blade version keyed each list's container by id, and the HQ and
            mailing blocks shared theirs — so "Add" under Mailing appended to
            the HQ list. Each mount owns its own array here.
        -->
        <div v-for="(value, index) in modelValue" :key="index" class="d-flex gap-2 mb-2">
            <input
                :value="value"
                :type="type"
                :placeholder="placeholder"
                class="form-control"
                @input="update(index, $event.target.value)"
            >
            <button type="button" class="btn btn-danger" @click="remove(index)">Remove</button>
        </div>

        <button type="button" class="btn btn-secondary btn-sm" @click="add">{{ addLabel }}</button>

        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>
