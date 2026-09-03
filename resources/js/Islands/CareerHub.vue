<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { openShare } from '@/lib/share';

const props = defineProps({
    // [{ index, title, description, image, curve: { viewBox, path }, topics: [{ id, title, description, image }] }]
    segments: { type: Array, default: () => [] },
    // route('career-topic', ['topic' => '__TOPIC_ID__'])
    topicUrlTemplate: { type: String, required: true },
    coreTitle: { type: String, default: '' },
    ringIcons: { type: Array, default: () => [] },
});

const root = ref(null);
const detailsCard = ref(null);

const activeIndex = ref(null);
const activeTopic = ref(null);

const activeSegment = computed(
    () => props.segments.find((segment) => segment.index === activeIndex.value) ?? null,
);

// The title is drawn on two lines in the core circle, as the Blade did with a
// str_replace on the space.
const coreLines = computed(() => props.coreTitle.split(' '));

const topicUrl = computed(() => (
    activeTopic.value?.id
        ? props.topicUrlTemplate.replace('__TOPIC_ID__', String(activeTopic.value.id))
        : ''
));

function selectSegment(index) {
    // Clicking the open segment closes the card.
    activeIndex.value = activeIndex.value === index ? null : index;
}

function openTopic(topic) {
    activeTopic.value = topic;
    document.body.style.overflow = 'hidden';
}

function closeTopic() {
    activeTopic.value = null;
    document.body.style.overflow = '';
}

function shareTopic() {
    if (!topicUrl.value) {
        return;
    }

    const url = topicUrl.value;
    const title = activeTopic.value?.title || 'Career Topic';
    const encodedUrl = encodeURIComponent(url);
    const encodedTitle = encodeURIComponent(title);

    openShare({
        url,
        title,
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`,
        twitter: `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedTitle}`,
        linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`,
        whatsapp: `https://api.whatsapp.com/send?text=${encodedTitle}%20${encodedUrl}`,
    });
}

function printTopic() {
    if (topicUrl.value) {
        window.open(`${topicUrl.value}?print=1`, '_blank');
    }
}

/**
 * The details card is absolutely positioned under the wheel, so when it is
 * taller than the wheel the page has to grow to make room for it.
 */
function adjustSpacing() {
    if (!root.value) {
        return;
    }

    const wheel = root.value.querySelector('.circle-and-details-card');

    if (!wheel) {
        return;
    }

    if (!detailsCard.value || activeIndex.value === null) {
        wheel.style.marginBottom = '';

        return;
    }

    const extraSpace = window.innerWidth < 992 ? 0 : 150;
    const needed = detailsCard.value.offsetHeight + extraSpace;
    const available = wheel.offsetHeight;

    wheel.style.marginBottom = needed > available ? `${needed - available}px` : '';
}

watch(activeIndex, () => nextTick(adjustSpacing));

function onKeydown(event) {
    if (event.key !== 'Escape') {
        return;
    }

    if (activeTopic.value) {
        closeTopic();
    } else if (activeIndex.value !== null) {
        activeIndex.value = null;
    }
}

/** Clicking away from the wheel closes the card, as before. */
function onDocumentClick(event) {
    if (activeIndex.value === null || activeTopic.value) {
        return;
    }

    const inside = event.target.closest(
        '.details-card, .segment, .ring-arc, .topic-modal-backdrop, #shareModal',
    );

    if (!inside) {
        activeIndex.value = null;
    }
}

onMounted(() => {
    document.addEventListener('keydown', onKeydown);
    document.addEventListener('click', onDocumentClick);
    window.addEventListener('resize', adjustSpacing);
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
    document.removeEventListener('click', onDocumentClick);
    window.removeEventListener('resize', adjustSpacing);
    document.body.style.overflow = '';
});
</script>

<template>
    <div ref="root">
        <div class="circle-and-details-card" :class="{ 'showing-card': activeIndex !== null }">
            <div class="circle-parent">
                <div class="hub">
                    <div
                        v-for="segment in segments"
                        :key="segment.index"
                        class="segment-outer-box"
                        :class="`segment-outer-box-${segment.index}`"
                    >
                        <div class="segment-dot-box">
                            <span class="segment-dot"></span>
                            <span class="segment-dot-path"></span>
                        </div>
                        <div
                            class="segment"
                            :class="[`segment-${segment.index}`, { 'add-border-on-click': activeIndex === segment.index }]"
                            role="button"
                            tabindex="0"
                            :aria-expanded="activeIndex === segment.index"
                            @click="selectSegment(segment.index)"
                            @keydown.enter.prevent="selectSegment(segment.index)"
                            @keydown.space.prevent="selectSegment(segment.index)"
                        >
                            <img :src="segment.image" :alt="segment.title">
                            <svg class="segment-curve" :viewBox="segment.curve.viewBox" aria-hidden="true">
                                <defs>
                                    <path :id="`seg-curve-${segment.index}`" :d="segment.curve.path" />
                                </defs>
                                <text>
                                    <textPath
                                        class="segment-text"
                                        :class="`segment-text-${segment.index}`"
                                        :href="`#seg-curve-${segment.index}`"
                                        startOffset="50%"
                                    >{{ segment.title }}</textPath>
                                </text>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="center-circle">
                    <div class="center-ring">
                        <div
                            v-for="(icon, i) in ringIcons"
                            :key="i"
                            class="ring-arc"
                            :class="`ring-arc-${i + 1}`"
                            role="button"
                            tabindex="0"
                            @click="selectSegment(i + 1)"
                            @keydown.enter.prevent="selectSegment(i + 1)"
                            @keydown.space.prevent="selectSegment(i + 1)"
                        >
                            <img class="ring-icon" :class="`ring-icon-${i + 1}`" :src="icon" :alt="`Category icon ${i + 1}`">
                        </div>
                    </div>
                    <div class="core-circle-container">
                        <div class="core-circle">
                            <span>
                                <template v-for="(line, i) in coreLines" :key="i">
                                    <br v-if="i > 0">{{ line }}
                                </template>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="details-cards-parent" :class="{ 'card-is-visible': activeIndex !== null }">
                <div ref="detailsCard" class="details-card">
                    <span class="details-card-header-back"></span>
                    <div class="details-card-header">
                        <span class="details-card-title">{{ activeSegment?.title }}</span>
                    </div>
                    <!-- Segment copy is authored in the admin's rich text editor. -->
                    <p class="details-card-desc" v-html="activeSegment?.description"></p>
                    <div class="details-card-list-parent-div">
                        <ul class="details-card-list">
                            <li v-if="!activeSegment?.topics?.length">No topics available.</li>
                            <li v-for="topic in activeSegment?.topics ?? []" :key="topic.id">
                                <span
                                    class="topic-item-link"
                                    role="button"
                                    tabindex="0"
                                    @click="openTopic(topic)"
                                    @keydown.enter.prevent="openTopic(topic)"
                                    @keydown.space.prevent="openTopic(topic)"
                                >{{ topic.title }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="topic-modal-backdrop" :class="{ 'is-visible': activeTopic }" @click.self="closeTopic">
            <div class="topic-modal" role="dialog" aria-modal="true" aria-labelledby="topic-modal-title">
                <button type="button" class="topic-modal-close" aria-label="Close" @click="closeTopic">x</button>
                <div class="topic-modal-content">
                    <img
                        v-if="activeTopic?.image"
                        :src="activeTopic.image"
                        alt="Topic image"
                        class="topic-modal-image"
                    >
                    <h4 id="topic-modal-title" class="topic-modal-title">{{ activeTopic?.title }}</h4>
                    <div class="topic-modal-description" v-html="activeTopic?.description"></div>
                    <hr>
                    <div class="topic-modal-footer">
                        <p>Copyright © 2026 Community Resource Directory. All rights reserved.</p>
                        <p>
                            No part of this text may be reproduced, distributed, or transmitted in any form or by
                            any means, including photocopying, recording, or other electronic or mechanical methods,
                            without the prior written permission of the publisher except for individual,
                            non-commercial, and informational use. This limited permission to recopy does not allow
                            you to modify or incorporate any portion of the contents in any work or publication
                            regardless of the medium. You may not recopy and share reproductions with a third party.
                        </p>
                    </div>
                    <div class="ico-box mt-3">
                        <button type="button" class="ico" title="Print" @click="printTopic">
                            <i class="fa-solid fa-print"></i>
                        </button>
                        <button type="button" class="ico" title="Share" @click="shareTopic">
                            <i class="fa-solid fa-share-nodes"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
