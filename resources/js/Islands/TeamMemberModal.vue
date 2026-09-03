<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const modalEl = ref(null);
const member = ref({ title: '', subtitle: '', image: '', description: '' });

let modal = null;

onMounted(() => {
    modal = new window.bootstrap.Modal(modalEl.value);
    // The cards stay server rendered — they carry the team's names and titles —
    // so the island listens for their triggers rather than owning them.
    document.addEventListener('click', onTrigger);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onTrigger);
    modal?.dispose();
});

function onTrigger(event) {
    const trigger = event.target.closest('.read-more-btn[data-title]');

    if (!trigger) {
        return;
    }

    event.preventDefault();

    member.value = {
        title: trigger.dataset.title ?? '',
        subtitle: trigger.dataset.subtitle ?? '',
        image: trigger.dataset.image ?? '',
        // The bio is rich text, base64'd into the attribute so its own markup
        // does not have to survive HTML escaping.
        description: decode(trigger.dataset.description),
    };

    modal?.show();
}

function decode(value) {
    if (!value) {
        return '';
    }

    try {
        return decodeURIComponent(escape(window.atob(value)));
    } catch (error) {
        return '';
    }
}
</script>

<template>
    <div
        id="teamPopup"
        ref="modalEl"
        class="modal fade"
        tabindex="-1"
        aria-labelledby="teamPopupLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7">
                            <h3 id="teamPopupLabel">{{ member.title }}</h3>
                            <p class="text-muted">{{ member.subtitle }}</p>
                            <div v-html="member.description"></div>
                        </div>
                        <div class="col-md-5 text-center">
                            <img
                                v-if="member.image"
                                :src="member.image"
                                class="img-fluid rounded mb-3"
                                :alt="member.title"
                                style="max-width: 100%;"
                            >
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>
