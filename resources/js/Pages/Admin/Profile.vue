<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

import FieldError from '@/Components/FieldError.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import PhoneInput from '@/Components/PhoneInput.vue';

const props = defineProps({
    submitUrl: { type: String, required: true },
    values: { type: Object, required: true },
    avatarUrl: { type: String, default: '' },
});

const form = useForm({
    ...props.values,
    profile_pic: null,
    reset_password: false,
    current_password: '',
    password: '',
    password_confirmation: '',
});

const avatarInput = ref(null);
const avatarPreview = ref(props.avatarUrl);

function onAvatar(event) {
    const file = event.target.files[0] ?? null;

    form.profile_pic = file;
    avatarPreview.value = file ? URL.createObjectURL(file) : props.avatarUrl;
}

function submit() {
    form
        .transform((data) => {
            const payload = { ...data, reset_password: data.reset_password ? 1 : '' };

            // Nothing about the password is sent unless the admin asked to
            // change it; the server keys its extra rules off the same flag.
            if (!data.reset_password) {
                delete payload.current_password;
                delete payload.password;
                delete payload.password_confirmation;
            }

            return payload;
        })
        .post(props.submitUrl, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => form.reset('current_password', 'password', 'password_confirmation'),
        });
}
</script>

<template>
    <PageHeader title="Profile" :breadcrumbs="[{ label: 'Home' }, { label: 'Profile' }]" />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Profile</h5>
                        </div>

                        <form class="px-3" @submit.prevent="submit">
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <img
                                        :src="avatarPreview"
                                        alt="Profile picture"
                                        class="rounded-circle"
                                        width="120"
                                        height="120"
                                    >
                                    <div class="mt-2">
                                        <input
                                            ref="avatarInput"
                                            type="file"
                                            class="d-none"
                                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                            @change="onAvatar"
                                        >
                                        <button type="button" class="btn btn-sm btn-primary" @click="avatarInput.click()">
                                            <i class="fas fa-upload"></i> Upload
                                        </button>
                                    </div>
                                    <FieldError :message="form.errors.profile_pic" />
                                </div>

                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="first_name">First Name</label>
                                        <input id="first_name" v-model="form.first_name" type="text" class="form-control" required>
                                        <FieldError :message="form.errors.first_name" />
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="last_name">Last Name</label>
                                        <input id="last_name" v-model="form.last_name" type="text" class="form-control">
                                        <FieldError :message="form.errors.last_name" />
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="email">Email</label>
                                        <input id="email" v-model="form.email" type="email" class="form-control" required>
                                        <FieldError :message="form.errors.email" />
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="phone">Phone</label>
                                        <PhoneInput id="phone" v-model="form.phone" />
                                        <FieldError :message="form.errors.phone" />
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input
                                        id="reset_password"
                                        v-model="form.reset_password"
                                        class="form-check-input"
                                        type="checkbox"
                                    >
                                    <label class="form-check-label" for="reset_password">Change my password</label>
                                </div>

                                <div v-if="form.reset_password" class="row">
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">Current Password</label>
                                        <PasswordInput v-model="form.current_password" placeholder="Current password" />
                                        <FieldError :message="form.errors.current_password" />
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">New Password</label>
                                        <PasswordInput
                                            v-model="form.password"
                                            placeholder="New password"
                                            autocomplete="new-password"
                                        />
                                        <FieldError :message="form.errors.password" />
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">Confirm Password</label>
                                        <PasswordInput
                                            v-model="form.password_confirmation"
                                            placeholder="Confirm password"
                                            autocomplete="new-password"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary save-btn" :disabled="form.processing">
                                    {{ form.processing ? 'Saving…' : 'Save' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
