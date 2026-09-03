<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthCard from '@/Components/Frontend/AuthCard.vue';
import FieldError from '@/Components/FieldError.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import { withRecaptcha } from '@/lib/recaptcha';

const props = defineProps({
    submitUrl: { type: String, required: true },
    bannerImage: { type: String, required: true },
    token: { type: String, required: true },
});

const form = useForm({
    token: props.token,
    user_email: '',
    password: '',
    password_confirmation: '',
    recaptcha_token: null,
});

function submit() {
    withRecaptcha(form, () => form.post(props.submitUrl, {
        onFinish: () => form.reset('password', 'password_confirmation'),
    }), 'reset_password');
}
</script>

<template>
    <Head title=" | Reset Password" />

    <AuthCard heading="Reset Password" :banner-image="bannerImage">
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

            <div class="df-column w-100">
                <PasswordInput
                    v-model="form.password"
                    placeholder="New Password*"
                    autocomplete="new-password"
                />
                <FieldError :message="form.errors.password" />
            </div>

            <div class="df-column w-100">
                <PasswordInput
                    v-model="form.password_confirmation"
                    placeholder="Confirm Password:*"
                    autocomplete="new-password"
                />
            </div>

            <FieldError :message="form.errors.token" />

            <button type="submit" class="primary-btn" :disabled="form.processing">
                {{ form.processing ? 'Saving…' : 'Submit' }}
            </button>
        </form>
    </AuthCard>
</template>
