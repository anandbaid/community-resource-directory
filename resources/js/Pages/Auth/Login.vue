<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthCard from '@/Components/Frontend/AuthCard.vue';
import FieldError from '@/Components/FieldError.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import { withRecaptcha } from '@/lib/recaptcha';

const props = defineProps({
    submitUrl: { type: String, required: true },
    registerUrl: { type: String, required: true },
    forgotUrl: { type: String, required: true },
    bannerImage: { type: String, required: true },
    // Already validated server side as a same-site path.
    redirect: { type: String, default: '' },
});

const form = useForm({
    login_email: '',
    login_password: '',
    redirect: props.redirect,
    recaptcha_token: null,
});

function submit() {
    withRecaptcha(form, () => form.post(props.submitUrl, {
        onFinish: () => form.reset('login_password'),
    }), 'login');
}
</script>

<template>
    <Head title=" | Login" />

    <AuthCard heading="Login" :banner-image="bannerImage">
        <form class="log-frm" @submit.prevent="submit">
            <div class="df-column w-100">
                <input
                    v-model="form.login_email"
                    type="email"
                    placeholder="Email Address*"
                    autocomplete="email"
                    required
                >
                <FieldError :message="form.errors.login_email" />

                <PasswordInput v-model="form.login_password" />
                <FieldError :message="form.errors.login_password" />
            </div>

            <div class="d-flex justify-content-between align-items-center w-100">
                <a :href="forgotUrl" class="textLink">Forgot Password</a>
            </div>

            <button type="submit" class="primary-btn login-btn" :disabled="form.processing">
                {{ form.processing ? 'Signing in…' : 'Login' }}
            </button>
        </form>

        <template #footer>
            <p class="sz-16">
                Don’t Have An Account? <a :href="registerUrl" class="fn-smb">Register Now</a>
            </p>
        </template>
    </AuthCard>
</template>
