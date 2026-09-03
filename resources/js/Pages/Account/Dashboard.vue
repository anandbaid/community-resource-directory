<script setup>
import AccountLayout from '@/Components/Frontend/AccountLayout.vue';

defineProps({
    quickLinks: { type: Object, required: true },
    bannerImage: { type: String, required: true },
    user: { type: Object, required: true },
    counts: { type: Object, required: true },
    links: { type: Object, required: true },
    suggestedOrganizations: { type: Array, default: () => [] },
});
</script>

<template>
    <AccountLayout
        :quick-links="quickLinks"
        :banner-image="bannerImage"
        banner-title="Dashboard"
        :banner-subtitle="`Welcome back, ${user.first_name || user.name}.`"
    >
        <div class="row gy-4">
            <div class="col-md-4" data-aos="fade-up">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body">
                        <p class="text-uppercase text-muted small mb-2">Profile</p>
                        <h5 class="mb-1">{{ user.name }}</h5>
                        <p class="mb-2 text-muted sz-16">{{ user.email }}</p>
                        <p class="mb-0 text-muted sz-16">
                            {{ user.phoneFormatted || 'Phone not added yet' }}
                        </p>
                        <a
                            :href="links.profile"
                            class="textLink text-decoration-none mt-3 d-inline-flex align-items-center gap-2"
                        >View Profile <i class="fa-solid fa-arrow-right-long"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-uppercase text-muted small">Saved Resources</span>
                            <a :href="links.savedResources" class="textLink">View all</a>
                        </div>
                        <h3 class="mb-3">{{ counts.savedResources }}</h3>
                        <p class="text-muted mb-0">Keep your favorite organizations together.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-uppercase text-muted small">Saved Searches</span>
                            <a :href="links.savedSearches" class="textLink">View all</a>
                        </div>
                        <h3 class="mb-3">{{ counts.savedSearches }}</h3>
                        <p class="text-muted mb-0">Reuse your most common searches quickly.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-4" data-aos="fade-up" data-aos-delay="150">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Suggested Organizations</h5>
                    <a :href="links.suggestNew" class="textLink">New suggestion</a>
                </div>

                <ul v-if="suggestedOrganizations.length" class="list-unstyled mb-0">
                    <li
                        v-for="(suggestion, index) in suggestedOrganizations"
                        :key="suggestion.id"
                        class="py-3"
                        :class="{ 'border-bottom': index !== suggestedOrganizations.length - 1 }"
                    >
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="me-3">
                                <div class="fw-semibold sz-16">{{ suggestion.name }}</div>
                                <div class="text-muted small">
                                    {{ suggestion.suggestionType }} suggestion &middot;
                                    Status: {{ suggestion.status }} &middot;
                                    Type: {{ suggestion.type }}
                                    <template v-if="suggestion.createdAt">&middot; {{ suggestion.createdAt }}</template>
                                </div>
                            </div>
                            <a v-if="suggestion.organizationUrl" :href="suggestion.organizationUrl" class="textLink">View</a>
                        </div>
                        <div v-if="suggestion.website" class="mt-2">
                            <a
                                :href="suggestion.website"
                                target="_blank"
                                rel="noopener"
                                class="textLink text-decoration-none"
                            >Website</a>
                        </div>
                    </li>
                </ul>

                <p v-else class="mb-0 text-muted">
                    You have not suggested any organizations yet.
                    <a :href="links.suggestNew" class="textLink">Submit one now</a>.
                </p>
            </div>
        </div>
    </AccountLayout>
</template>
