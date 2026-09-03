<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthCard from '@/Components/Frontend/AuthCard.vue';
import FieldError from '@/Components/FieldError.vue';
import PhoneInput from '@/Components/PhoneInput.vue';
import { withRecaptcha } from '@/lib/recaptcha';

const props = defineProps({
    submitUrl: { type: String, required: true },
    loginUrl: { type: String, required: true },
    bannerImage: { type: String, required: true },
});

const form = useForm({
    register_name: '',
    register_phone: '',
    register_email: '',
    register_zip: '',
    recaptcha_token: null,
});

function submit() {
    withRecaptcha(form, () => form.post(props.submitUrl), 'register');
}
</script>

<template>
    <Head title=" | Register" />

    <AuthCard heading="Register" :banner-image="bannerImage" wide>
        <template #intro>
            <div class="mb-4">
                <p>
                    Thank you for choosing to register for a user account!<br>
                    Your registration will ensure you have the best possible user experience. Registration
                    allows you to access features such as creating a custom resource list, recommending new
                    resources and suggesting edits to existing resources. Additionally, registration allows us
                    to better meet your needs through periodic surveys and promotions.<br>
                    Registration is free.
                </p>
            </div>
        </template>

        <form class="reg-frm" @submit.prevent="submit">
            <div class="df-column align-items-center">
                <input v-model="form.register_name" type="text" placeholder="Your Name*" autocomplete="name" required>
                <FieldError :message="form.errors.register_name" />

                <PhoneInput v-model="form.register_phone" placeholder="Phone Number*" />
                <FieldError :message="form.errors.register_phone" />

                <input v-model="form.register_email" type="email" placeholder="Email Address*" autocomplete="email" required>
                <FieldError :message="form.errors.register_email" />

                <input v-model="form.register_zip" type="text" placeholder="Zip code*" autocomplete="postal-code" required>
                <FieldError :message="form.errors.register_zip" />

                <button type="submit" class="primary-btn mt-3 register-btn" :disabled="form.processing">
                    {{ form.processing ? 'Registering…' : 'Register' }}
                </button>
            </div>
        </form>

        <template #footer>
            <p class="sz-16">Already Have An Account? <a :href="loginUrl" class="fn-smb">Login</a></p>
            <p>
                <span class="fn-smb">Privacy and Security:</span> Our Promise to You.
                Maintaining your privacy and security is our top priority. Information furnished through the
                registration process and/or while an active user of our site will never be shared with
                third-parties. Personally identifiable information (PII) such as name, email address, phone and
                postal code are encrypted and maintained in accordance with our industry leading privacy and
                data security policies.
            </p>
        </template>
    </AuthCard>
</template>
