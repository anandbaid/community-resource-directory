<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { openShare, shareFromElement, shareState } from '@/lib/share';

const modalEl = ref(null);
const copied = ref(false);

let modal = null;
let copyTimer = null;

onMounted(() => {
    modal = new window.bootstrap.Modal(modalEl.value);

    // Pages still rendering their own trigger markup keep working; the handler
    // is delegated so it covers Vue-rendered triggers on this page too.
    document.addEventListener('click', onLegacyTrigger);

    // Escape hatch for the pages whose share links are still computed in inline
    // Blade scripts (career, career topics).
    window.openShare = openShare;
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onLegacyTrigger);
    delete window.openShare;
    clearTimeout(copyTimer);
    modal?.dispose();
});

function onLegacyTrigger(event) {
    const trigger = event.target.closest('.share-trigger[data-url]');

    if (!trigger) {
        return;
    }

    event.preventDefault();
    Object.assign(shareState, shareFromElement(trigger));
    shareState.requests += 1;
}

watch(() => shareState.requests, () => {
    copied.value = false;
    modal?.show();
});

async function copyLink() {
    if (!shareState.url) {
        return;
    }

    try {
        await navigator.clipboard.writeText(shareState.url);
    } catch (error) {
        // Clipboard access needs a secure context; fall back to the old trick.
        legacyCopy(shareState.url);
    }

    copied.value = true;
    clearTimeout(copyTimer);
    copyTimer = setTimeout(() => (copied.value = false), 1500);
}

function legacyCopy(text) {
    const scratch = document.createElement('textarea');

    scratch.value = text;
    scratch.setAttribute('readonly', '');
    scratch.style.position = 'absolute';
    scratch.style.left = '-10000px';

    document.body.appendChild(scratch);
    scratch.select();

    try {
        document.execCommand('copy');
    } catch (error) {
        console.warn('Copy failed', error);
    }

    scratch.remove();
}
</script>

<template>
    <div
        id="shareModal"
        ref="modalEl"
        class="modal fade"
        tabindex="-1"
        aria-labelledby="shareModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog share-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="shareModalLabel" class="modal-title">{{ shareState.title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="share-input-group">
                            <input :value="shareState.url" type="text" class="form-control" readonly>
                            <button type="button" class="primary-btn copy-btn" @click="copyLink">
                                {{ copied ? 'Copied!' : 'Copy' }}
                            </button>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a
                            v-if="shareState.facebook"
                            :href="shareState.facebook"
                            class="ico"
                            target="_blank"
                            rel="noopener"
                            title="Share on Facebook"
                        ><i class="fa-brands fa-facebook-f"></i></a>
                        <a
                            v-if="shareState.twitter"
                            :href="shareState.twitter"
                            class="ico"
                            target="_blank"
                            rel="noopener"
                            title="Share on X/Twitter"
                        ><i class="fa-brands fa-twitter"></i></a>
                        <a
                            v-if="shareState.linkedin"
                            :href="shareState.linkedin"
                            class="ico"
                            target="_blank"
                            rel="noopener"
                            title="Share on LinkedIn"
                        ><i class="fa-brands fa-linkedin-in"></i></a>
                        <a
                            v-if="shareState.whatsapp"
                            :href="shareState.whatsapp"
                            class="ico"
                            target="_blank"
                            rel="noopener"
                            title="Share on WhatsApp"
                        ><i class="fa-brands fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
