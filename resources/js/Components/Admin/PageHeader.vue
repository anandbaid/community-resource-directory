<script setup>
defineProps({
    title: { type: String, required: true },
    // [{ label, url }] — the last entry renders as the active crumb.
    breadcrumbs: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">{{ title }}</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li v-for="(crumb, index) in breadcrumbs" :key="index" class="breadcrumb-item"
                            :class="{ active: index === breadcrumbs.length - 1 }"
                            :aria-current="index === breadcrumbs.length - 1 ? 'page' : undefined">
                            <a v-if="crumb.url && index !== breadcrumbs.length - 1" :href="crumb.url">{{ crumb.label }}</a>
                            <template v-else>{{ crumb.label }}</template>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</template>
