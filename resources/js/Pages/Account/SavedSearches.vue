<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

import AccountLayout from '@/Components/Frontend/AccountLayout.vue';

const props = defineProps({
    quickLinks: { type: Object, required: true },
    // [{ id, label, searchUrl, downloadUrl, deleteUrl }]
    savedSearches: { type: Array, default: () => [] },
});

const deletingId = ref(null);

async function confirmDelete(savedSearch) {
    const result = await window.Swal.fire({
        title: 'Are you sure you want to delete the saved search list',
        icon: 'warning',
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes',
    });

    if (!result.isConfirmed) {
        return;
    }

    deletingId.value = savedSearch.id;

    router.delete(savedSearch.deleteUrl, {
        preserveScroll: true,
        onFinish: () => {
            deletingId.value = null;
        },
    });
}
</script>

<template>
    <AccountLayout :quick-links="quickLinks" heading="Saved Search Results">
        <div class="white-box resource-frm-container" data-aos="fade-up" data-aos-delay="100">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Search Results</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(savedSearch, index) in savedSearches"
                        :key="savedSearch.id"
                        data-aos="fade-up"
                        :data-aos-delay="100 + index * 50"
                    >
                        <td>{{ index + 1 }}</td>
                        <td>
                            <a :href="savedSearch.searchUrl">
                                Search - Based on {{ savedSearch.label }}
                            </a>
                        </td>
                        <td>
                            <a :href="savedSearch.downloadUrl" class="download-search">
                                <i class="fa-solid fa-download"></i>
                            </a>
                            <a
                                href="javascript:void(0);"
                                class="delete-search"
                                :aria-disabled="deletingId === savedSearch.id"
                                @click="confirmDelete(savedSearch)"
                            >
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>

                    <tr v-if="savedSearches.length === 0">
                        <td colspan="3" class="text-center">No records found</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AccountLayout>
</template>
