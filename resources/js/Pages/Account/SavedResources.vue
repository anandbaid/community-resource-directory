<script setup>
import AccountLayout from '@/Components/Frontend/AccountLayout.vue';
import ResourceCard from '@/Components/Frontend/ResourceCard.vue';

defineProps({
    quickLinks: { type: Object, required: true },
    resources: { type: Array, default: () => [] },
});
</script>

<template>
    <AccountLayout :quick-links="quickLinks" heading="Saved Resources">
        <div class="row gx-4 gy-4 saved-resources-grid">
            <div
                v-for="resource in resources"
                :key="resource.id"
                class="col-lg-6"
                data-aos="fade-up"
            >
                <ResourceCard :resource="resource" show-actions />
            </div>

            <div v-if="resources.length === 0" class="col-12 text-center no-saved-message">
                No saved resources.
            </div>
        </div>
    </AccountLayout>
</template>
