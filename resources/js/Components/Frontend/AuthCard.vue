<script setup>
defineProps({
    heading: { type: String, required: true },
    bannerImage: { type: String, required: true },
    // The register page needs a wider, left-aligned body for its copy.
    wide: { type: Boolean, default: false },
});
</script>

<template>
    <main>
        <section>
            <div class="bannerBack innerBanner" :style="{ backgroundImage: `url(${bannerImage})` }">
                <div class="container banContainer">
                    <div class="df-column bannerCont">
                        <h2 class="bannerHead">{{ heading }}</h2>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="container">
                <div class="panel">
                    <div class="text-center" :class="wide ? 'reg-content' : 'cntctFrmSec'">
                        <slot name="intro"></slot>

                        <div class="dark-frm">
                            <h3>{{ heading }}</h3>
                            <slot></slot>
                        </div>

                        <slot name="footer"></slot>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>
