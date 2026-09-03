<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    // Laravel paginator ->linkCollection(): [{ url, label, active }]
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <nav v-if="links.length > 3" aria-label="Pages">
        <ul class="pagination mb-0">
            <li
                v-for="(link, index) in links"
                :key="index"
                class="page-item"
                :class="{ active: link.active, disabled: !link.url }"
            >
                <Link v-if="link.url" class="page-link" :href="link.url" preserve-scroll v-html="link.label" />
                <span v-else class="page-link" v-html="link.label" />
            </li>
        </ul>
    </nav>
</template>
