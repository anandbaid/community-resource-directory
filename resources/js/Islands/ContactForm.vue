<script setup>
import { reactive, ref } from 'vue';
import FieldError from '@/Components/FieldError.vue';
import { swalAlert } from '@/lib/flash';
import { recaptchaToken } from '@/lib/recaptcha';

const props = defineProps({
    submitUrl: { type: String, required: true },
});

function blank() {
    return {
        first_name: '',
        last_name: '',
        organization: '',
        email: '',
        message: '',
    };
}

const form = reactive(blank());
const errors = ref({});
const submitting = ref(false);

async function submit() {
    if (submitting.value) {
        return;
    }

    submitting.value = true;
    errors.value = {};

    try {
        const { data } = await window.axios.post(
            props.submitUrl,
            {
                ...form,
                // Minted here rather than at page load: v3 tokens go stale in
                // about two minutes and this form takes longer than that to fill.
                recaptcha_token: await recaptchaToken('contact_us'),
            },
            { headers: { 'X-CSRF-TOKEN': window.csrf_token } },
        );

        Object.assign(form, blank());
        swalAlert(data.status ?? 'success', data.message ?? 'Thanks — we have your message.', 3000);
    } catch (error) {
        const body = error.response?.data;

        if (body?.errors && typeof body.errors === 'object') {
            // Laravel's 422 shape: one array of messages per field.
            errors.value = Object.fromEntries(
                Object.entries(body.errors).map(([field, messages]) => [field, messages[0]]),
            );
        } else {
            swalAlert('error', body?.errors ?? 'An unexpected error occurred.', 7000);
        }
    } finally {
        submitting.value = false;
    }
}

function reset() {
    Object.assign(form, blank());
    errors.value = {};
}
</script>

<template>
    <div class="cntctFrmBox">
        <h2><span class="lt">Fill </span>The Form</h2>

        <form @submit.prevent="submit">
            <div class="row gy-3">
                <div class="col-lg-6">
                    <input v-model="form.first_name" type="text" placeholder="First Name *" autocomplete="given-name" required>
                    <FieldError :message="errors.first_name" />
                </div>
                <div class="col-lg-6">
                    <input v-model="form.last_name" type="text" placeholder="Last Name *" autocomplete="family-name" required>
                    <FieldError :message="errors.last_name" />
                </div>
                <div class="col-12">
                    <input v-model="form.organization" type="text" placeholder=" Agency / Organization*" required>
                    <FieldError :message="errors.organization" />
                </div>
                <div class="col-12">
                    <input v-model="form.email" type="email" placeholder="Email Address *" autocomplete="email" required>
                    <FieldError :message="errors.email" />
                </div>
                <div class="col-12">
                    <textarea v-model="form.message" placeholder="Type Your Message" maxlength="2000" required></textarea>
                    <FieldError :message="errors.message" />
                </div>
                <div class="col-12 btn-group mt-4">
                    <button type="submit" class="primary-btn submit-btn" :disabled="submitting">
                        {{ submitting ? 'Sending…' : 'Submit' }}
                    </button>
                    <button type="button" class="secondary-btn" @click="reset">Reset</button>
                </div>
            </div>
        </form>
    </div>
</template>
