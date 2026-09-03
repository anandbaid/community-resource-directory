<script setup>
import { computed, ref } from 'vue';
import { openShare } from '@/lib/share';
import { swalAlert } from '@/lib/flash';

const props = defineProps({
    // First page of cards, already shaped by OrganizationController::publicationCard().
    publications: { type: Array, default: () => [] },
    total: { type: Number, default: 0 },
    loadMoreUrl: { type: String, required: true },
});

const items = ref([...props.publications]);
const loading = ref(false);
const exhausted = ref(props.publications.length >= props.total);

const canLoadMore = computed(() => !exhausted.value && items.value.length < props.total);

async function loadMore() {
    if (loading.value) {
        return;
    }

    loading.value = true;

    try {
        const { data } = await window.axios.get(props.loadMoreUrl, {
            params: { items: items.value.length },
        });

        items.value = items.value.concat(data.publications ?? []);

        if (data.hideBtn) {
            exhausted.value = true;
        }
    } catch (error) {
        swalAlert('error', 'Could not load more publications. Please try again.', 4000);
    } finally {
        loading.value = false;
    }
}

function print(event, fileUrl) {
    window.printFile(event, fileUrl);
}
</script>

<template>
    <div>
        <div class="row gy-4 publication-section">
            <div v-for="publication in items" :key="publication.id" class="col-xl-4 col-sm-6">
                <div class="pub-box">
                    <img :src="publication.image" :alt="publication.title" class="pub-img">
                    <div class="pub-cont">
                        <div class="org-bd">{{ publication.title }}</div>
                        <div v-if="publication.file" class="ico-box justify-content-center">
                            <a :href="publication.downloadUrl" class="ico" title="Download">
                                <i class="fa-solid fa-download"></i>
                            </a>
                            <a href="#" class="ico" title="Print" @click="print($event, publication.file)">
                                <i class="fa-solid fa-print"></i>
                            </a>
                            <button type="button" class="ico" title="Share" @click="openShare(publication.share)">
                                <i class="fa-solid fa-share-nodes"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="canLoadMore" class="text-center mt-4">
            <button type="button" class="load-more-publication border-btn" :disabled="loading" @click="loadMore">
                {{ loading ? 'Loading…' : 'View All' }}
            </button>
        </div>
    </div>
</template>
