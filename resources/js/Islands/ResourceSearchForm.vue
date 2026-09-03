<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    action: { type: String, required: true },
    suggestUrl: { type: String, required: true },
    states: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    // Current query string values, so a reloaded search comes back filled in.
    query: { type: Object, default: () => ({}) },
    // The home page shows the same box without the extra filters.
    showAdvanced: { type: Boolean, default: true },
});

const form = ref({
    state: props.query.state ?? '',
    postal_code: props.query.postal_code ?? '',
    category: props.query.category ?? '',
    organization_type: props.query.organization_type ?? '',
    target_population: props.query.target_population ?? '',
    organization_name: props.query.organization_name ?? '',
});

// The advanced block starts open when the last search used it.
const advanced = ref(Boolean(props.query.advance));
const postalError = ref('');

const POSTAL_PATTERN = /^\d{5}(-\d{4})?$/;

const sortValue = computed(() => props.query.sort ?? '');

function submit(event) {
    postalError.value = '';

    if (form.value.postal_code && !POSTAL_PATTERN.test(form.value.postal_code)) {
        postalError.value = 'Please enter a postal code in the format XXXXX or XXXXX-XXXX.';
        event.preventDefault();
    }
}
</script>

<template>
    <div class="dropBox shadowBox">
        <div class="dropBoxCont">
            <div class="resourceFrmCont">
                <div data-aos="fade-right">
                    <h3 class="admin-head mb-2">Find a Resource</h3>
                    <p class="m-0">Search thousands of verified community resources.</p>
                </div>
                <a :href="suggestUrl" data-aos="fade-left">
                    Are we missing a useful Resource? Let us know!
                </a>
            </div>

            <form
                class="mt-4 resourceFrm"
                data-aos="fade-up"
                name="serach_resource"
                :action="action"
                method="get"
                @submit="submit"
            >
                <div class="row gy-3 advance-search-frm">
                    <div class="col-lg-3">
                        <select v-model="form.state" name="state" required>
                            <option value="">Select State</option>
                            <option v-for="state in states" :key="state" :value="state">{{ state }}</option>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <input
                            v-model="form.postal_code"
                            type="text"
                            name="postal_code"
                            placeholder="Postal code"
                        >
                    </div>

                    <div class="col-lg-3">
                        <select v-model="form.category" name="category">
                            <option value="">Select Categories</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <template v-if="showAdvanced && advanced">
                        <div class="col-lg-3 adv-search-inp">
                            <select v-model="form.organization_type" name="organization_type">
                                <option value="">Select Organization Type</option>
                                <option value="government">Government</option>
                                <option value="non-government">Non-Government</option>
                            </select>
                        </div>

                        <div class="col-lg-3 adv-search-inp">
                            <select v-model="form.target_population" name="target_population">
                                <option value="">Select Target Population</option>
                                <option value="Adult">Adult</option>
                                <option value="Youth">Youth</option>
                                <option value="Justice Impacted">Justice Impacted</option>
                            </select>
                        </div>

                        <div class="col-lg-3 adv-search-inp">
                            <input
                                v-model="form.organization_name"
                                type="text"
                                name="organization_name"
                                placeholder="Enter Organization Name"
                            >
                        </div>

                        <input type="hidden" name="advance" value="1">
                    </template>

                    <!-- Carry the current sort through so searching does not reset it. -->
                    <input v-if="sortValue" type="hidden" name="sort" :value="sortValue">

                    <div class="col-lg-3 advance-search-frm-submit">
                        <input type="submit" class="primary-btn search-btn" value="Search">
                    </div>

                    <div v-if="postalError" class="col-12">
                        <span class="text-danger">{{ postalError }}</span>
                    </div>
                </div>

                <span
                    v-if="showAdvanced"
                    class="pt-4 adv-search-btn"
                    :class="{ active: advanced }"
                    data-aos="fade-up"
                    role="button"
                    tabindex="0"
                    @click="advanced = !advanced"
                    @keydown.enter.prevent="advanced = !advanced"
                    @keydown.space.prevent="advanced = !advanced"
                >Advanced Search </span>
            </form>
        </div>
    </div>
</template>
