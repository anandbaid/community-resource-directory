<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { swalAlert } from '@/lib/flash';
import { recaptchaToken } from '@/lib/recaptcha';

const props = defineProps({
    orgId: { type: [Number, String], required: true },
    submitUrl: { type: String, required: true },
});

const MAX_REASON = 350;

const modalEl = ref(null);
const reason = ref('');
const error = ref('');
const submitting = ref(false);

let modal = null;

onMounted(() => {
    modal = new window.bootstrap.Modal(modalEl.value);
    modalEl.value.addEventListener('hidden.bs.modal', reset);
});

onBeforeUnmount(() => {
    modalEl.value?.removeEventListener('hidden.bs.modal', reset);
    modal?.dispose();
});

function reset() {
    reason.value = '';
    error.value = '';
}

function open() {
    reset();
    modal?.show();
}

async function submit() {
    if (submitting.value) {
        return;
    }

    if (!reason.value.trim()) {
        error.value = 'Please enter a reason.';

        return;
    }

    submitting.value = true;
    error.value = '';

    try {
        const { data } = await window.axios.post(
            props.submitUrl,
            {
                org_id: props.orgId,
                spam_reason: reason.value.trim(),
                // v3 tokens expire in about two minutes, so mint one here rather
                // than reusing whatever the page was loaded with.
                recaptcha_token: await recaptchaToken('report_spam'),
            },
            { headers: { 'X-CSRF-TOKEN': window.csrf_token } },
        );

        modal?.hide();
        swalAlert(data.status ?? 'success', data.message ?? 'Report submitted successfully', 3000);
    } catch (e) {
        error.value = e.response?.data?.errors ?? 'There was a problem, try again later.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <span
        class="ico sm-ico"
        role="button"
        tabindex="0"
        title="Report as Spam"
        @click="open"
        @keydown.enter.prevent="open"
        @keydown.space.prevent="open"
    >
        <i class="fa-solid fa-shield-halved"></i>
    </span>

    <!--
        Teleported because the surrounding card is animated with a CSS transform,
        and a transformed ancestor breaks the modal's fixed positioning.
    -->
    <Teleport to="body">
        <div ref="modalEl" class="modal fade" tabindex="-1" aria-labelledby="reportSpamLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 id="reportSpamLabel" class="modal-title fs-5">Report as Spam</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="px-3" @submit.prevent="submit">
                            <textarea
                                v-model="reason"
                                cols="30"
                                rows="2"
                                class="form-control"
                                :class="{ 'is-invalid': error }"
                                :maxlength="MAX_REASON"
                                placeholder="Please enter a reason."
                            ></textarea>
                            <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
                            <div class="text-muted small mt-1">{{ reason.length }}/{{ MAX_REASON }}</div>
                            <br>
                            <button type="submit" class="primary-btn" :disabled="submitting">
                                {{ submitting ? 'Submitting…' : 'Submit' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
