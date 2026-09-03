<script setup>
import PageHeader from '@/Components/Admin/PageHeader.vue';

defineProps({
    stats: { type: Array, default: () => [] },
});

// The Blade view inlined two 600-character SVG paths, one of them twice.
const ICONS = {
    users: 'M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304l91.4 0C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7L29.7 512C13.3 512 0 498.7 0 482.3zM609.3 512l-137.8 0c5.4-9.4 8.6-20.3 8.6-32l0-8c0-60.7-27.1-115.2-69.8-151.8c2.4-.1 4.7-.2 7.1-.2l61.4 0C567.8 320 640 392.2 640 481.3c0 17-13.8 30.7-30.7 30.7zM432 256c-31 0-59-12.6-79.3-32.9C372.4 196.5 384 163.6 384 128c0-26.8-6.6-52.1-18.3-74.3C384.3 40.1 407.2 32 432 32c61.9 0 112 50.1 112 112s-50.1 112-112 112z',
    clipboard: 'M192 0c-41.8 0-77.4 26.7-90.5 64L64 64C28.7 64 0 92.7 0 128L0 448c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64l-37.5 0C269.4 26.7 233.8 0 192 0zm0 64a32 32 0 1 1 0 64 32 32 0 1 1 0-64zM112 192l160 0c8.8 0 16 7.2 16 16s-7.2 16-16 16l-160 0c-8.8 0-16-7.2-16-16s7.2-16 16-16z',
};

const VIEW_BOX = {
    users: '0 0 640 512',
    clipboard: '0 0 384 512',
};
</script>

<template>
    <PageHeader title="Dashboard" :breadcrumbs="[{ label: 'Home' }, { label: 'Dashboard' }]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div v-for="stat in stats" :key="stat.label" class="col-lg-3 col-6">
                    <div class="small-box" :class="`text-bg-${stat.tone}`">
                        <div class="inner">
                            <h3>{{ stat.value }}</h3>
                            <p>{{ stat.label }}</p>
                        </div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="small-box-icon"
                            fill="currentColor"
                            aria-hidden="true"
                            :viewBox="VIEW_BOX[stat.icon]"
                        >
                            <path :d="ICONS[stat.icon]" />
                        </svg>
                        <a
                            :href="stat.url"
                            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                        >
                            More info <i class="bi bi-link-45deg"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
