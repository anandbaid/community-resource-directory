<script setup>
import { swalAlert } from '@/lib/flash';

const props = defineProps({
    links: { type: Object, required: true },
});

function onDownloadUnavailable() {
    swalAlert('info', 'No saved resource found.');
}
</script>

<template>
    <div class="card shadow-sm h-100 border-0">
        <div class="card-body">
            <h5 class="mb-3">Quick Links</h5>
            <ul class="list-unstyled mb-0">
                <li class="py-2 border-bottom">
                    <a :href="links.dashboardUrl" class="d-flex justify-content-between align-items-center textLink text-decoration-none">
                        <span>Dashboard</span>
                        <i class="fa-solid fa-arrow-right-long"></i>
                    </a>
                </li>
                <li class="py-2 border-bottom">
                    <a :href="links.profileUrl" class="d-flex justify-content-between align-items-center textLink text-decoration-none">
                        <span>View Profile</span>
                        <i class="fa-regular fa-user"></i>
                    </a>
                </li>
                <li class="py-2 border-bottom">
                    <a :href="links.savedResourcesUrl" class="d-flex justify-content-between align-items-center textLink text-decoration-none">
                        <span>Saved Resources</span>
                        <i class="fa-regular fa-floppy-disk"></i>
                    </a>
                </li>
                <li class="py-2 border-bottom">
                    <a
                        v-if="links.downloadSavedResourcesUrl"
                        :href="links.downloadSavedResourcesUrl"
                        class="d-flex justify-content-between align-items-center textLink text-decoration-none"
                    >
                        <span>Download Saved Resources</span>
                        <i class="fa-solid fa-download"></i>
                    </a>
                    <a
                        v-else
                        href="javascript:void(0);"
                        class="d-flex justify-content-between align-items-center textLink text-decoration-none"
                        @click="onDownloadUnavailable"
                    >
                        <span>Download Saved Resources</span>
                        <i class="fa-solid fa-download"></i>
                    </a>
                </li>
                <li class="py-2 border-bottom">
                    <a :href="links.savedSearchesUrl" class="d-flex justify-content-between align-items-center textLink text-decoration-none">
                        <span>Saved Searches</span>
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                </li>
                <li class="py-2 border-bottom">
                    <a :href="links.suggestNewUrl" class="d-flex justify-content-between align-items-center textLink text-decoration-none">
                        <span>Recommend a Resource</span>
                        <i class="fa-regular fa-rectangle-list"></i>
                    </a>
                </li>
                <li class="py-2">
                    <a :href="links.suggestExistingUrl" class="d-flex justify-content-between align-items-center textLink text-decoration-none">
                        <span>Suggest an Edit</span>
                        <i class="fa-regular fa-pen-to-square"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>
