<script setup>
import QuickLinks from '@/Components/Frontend/QuickLinks.vue';

defineProps({
    quickLinks: { type: Object, required: true },
    // Omit both banner props to render the plain centred-heading variant.
    bannerImage: { type: String, default: '' },
    bannerTitle: { type: String, default: '' },
    bannerSubtitle: { type: String, default: '' },
    heading: { type: String, default: '' },
});
</script>

<template>
    <main>
        <section v-if="bannerImage">
            <div class="bannerBack innerBanner" :style="{ backgroundImage: `url(${bannerImage})` }">
                <div class="container banContainer">
                    <div class="df-column bannerCont" data-aos="fade-up">
                        <h2 class="bannerHead">{{ bannerTitle }}</h2>
                        <p v-if="bannerSubtitle" class="banPara">{{ bannerSubtitle }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="container panel bg-container panel-bottom">
                <div class="row gy-4 gx-4">
                    <div class="col-lg-3" data-aos="fade-up">
                        <QuickLinks :links="quickLinks" />
                    </div>

                    <div class="col-lg-9">
                        <div v-if="heading" class="text-center mb-50">
                            <h3 data-aos="fade-up" class="text-center admin-head">{{ heading }}</h3>
                        </div>

                        <slot />
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>
