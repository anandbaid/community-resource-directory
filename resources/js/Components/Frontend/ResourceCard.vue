<script setup>
import SaveResourceToggle from '@/Islands/SaveResourceToggle.vue';

defineProps({
    /**
     * {
     *   id, name, state, type, categories[], description, publications[],
     *   ratingStars (html), ratingCount, isMember,
     *   detailsUrl, reviewUrl, saveUrl, saved, rated
     * }
     */
    resource: { type: Object, required: true },
    memberBadge: { type: String, default: '' },
    // Signed-in, non-admin visitors get the rate/save affordances.
    showActions: { type: Boolean, default: false },
});
</script>

<template>
    <div class="search-box">
        <div class="ico-box search-wish-box" :class="{ 'member-exists': resource.isMember }">
            <template v-if="showActions">
                <span v-if="resource.rated" class="ico sm-ico active">
                    <i class="fa-regular fa-thumbs-down"></i>
                </span>
                <a v-else :href="resource.reviewUrl" class="ico sm-ico">
                    <i class="fa-regular fa-thumbs-down"></i>
                </a>

                <SaveResourceToggle :url="resource.saveUrl" :saved="resource.saved" />
            </template>

            <span v-if="resource.isMember && memberBadge" class="member_tag">
                <img :src="memberBadge" class="member_icon" alt="Member">
            </span>
        </div>

        <h6>{{ resource.name }}</h6>

        <div class="org-location-box">
            <span>
                <i class="fa-solid fa-location-dot"></i>
                <span class="org-loc"> {{ resource.state }}</span>
            </span>
            <span>
                <!-- Star markup is produced by CommonFunction::getRatingStars(). -->
                <span class="org-rating" v-html="resource.ratingStars"></span>
                ({{ resource.ratingCount }} Ratings)
            </span>
        </div>

        <div>
            <span class="org-bd">Organization Type: </span>{{ resource.type }}
        </div>
        <div class="one-line-ellip">
            <span class="org-bd">Service Categories: </span>{{ resource.categories.join(', ') }}
        </div>
        <div class="two-line-ellip">
            <span class="org-bd">Description: </span>{{ resource.description }}
        </div>
        <div class="one-line-ellip">
            <span class="org-bd">Publications: </span>{{ resource.publications.join(', ') }}
        </div>

        <a :href="resource.detailsUrl" class="redLink">View Details</a>
    </div>
</template>
