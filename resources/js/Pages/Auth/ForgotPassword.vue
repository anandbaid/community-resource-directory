<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthCard from '@/Components/Frontend/AuthCard.vue';
import FieldError from '@/Components/FieldError.vue';
import { withRecaptcha } from '@/lib/recaptcha';

const props = defineProps({
    submitUrl: { type: String, required: true },
    bannerImage: { type: String, required: true },
});

const form = useForm({
    user_email: '',
    recaptcha_token: null,
});

function submit() {
    withRecaptcha(form, () => form.post(props.submitUrl), 'forgot_password');
}
</script>

<template>
    <Head title=" | Forgot Password" />

    <AuthCard heading="Forgot Password" :banner-image="bannerImage">
        <form class="log-frm" @submit.prevent="submit">
            <div class="df-column w-100">
                <input
                    v-model="form.user_email"
                    type="email"
                    placeholder="Email Address*"
                    autocomplete="email"
                    required
                >
                <FieldError :message="form.errors.user_email" />
            </div>

            <button type="submit" class="primary-btn" :disabled="form.processing">
                {{ form.processing ? 'Sending…' : 'Submit' }}
            </button>
        </form>
    </AuthCard>
</template>
