<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

import FieldError from '@/Components/FieldError.vue';
import PageHeader from '@/Components/Admin/PageHeader.vue';
import PhoneInput from '@/Components/PhoneInput.vue';

const props = defineProps({
    type: { type: String, required: true },
    submitUrl: { type: String, required: true },
    indexUrl: { type: String, required: true },
    placeholderImage: { type: String, required: true },
    avatarUrl: { type: String, default: '' },
    values: { type: Object, required: true },
});

const isEdit = props.type === 'Edit';

const form = useForm({
    ...(isEdit ? { _method: 'put' } : {}),
    ...props.values,
    profile_pic: null,
});

const fileInput = ref(null);
const preview = ref(props.avatarUrl || props.placeholderImage);

// Create always sets a password; edit only does when the admin opts in.
const resetPassword = ref(!isEdit);

watch(resetPassword, (on) => {
    form.reset_password = isEdit && on;

    if (!on) {
        form.password = '';
        form.password_confirmation = '';
    }
});

function onFileChange(event) {
    const file = event.target.files[0] ?? null;

    form.profile_pic = file;
    preview.value = file ? URL.createObjectURL(file) : props.avatarUrl || props.placeholderImage;
}

function submit() {
    form.post(props.submitUrl, { forceFormData: true, preserveScroll: true });
}
</script>

<template>
    <PageHeader
        :title="isEdit ? 'Modify User' : 'Create User'"
        :breadcrumbs="[{ label: 'Users', url: indexUrl }, { label: isEdit ? 'Modify User' : 'Create User' }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mt-2 mb-0 text-center">
                                {{ isEdit ? 'Modify User Details' : 'User Details' }}
                            </h4>
                        </div>

                        <form class="px-3" @submit.prevent="submit">
                            <div class="card-body">
                                <div class="text-center">
                                    <img
                                        :src="preview"
                                        alt=""
                                        class="img-responsive mt-2"
                                        width="120"
                                        height="120"
                                    >
                                    <div class="mt-2">
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            class="d-none"
                                            accept="image/jpeg,image/png,image/jpg,image/webp"
                                            @change="onFileChange"
                                        >
                                        <button type="button" class="btn btn-sm btn-primary" @click="fileInput.click()">
                                            <i class="fas fa-upload"></i> Upload
                                        </button>
                                    </div>
                                    <FieldError :message="form.errors.profile_pic" />
                                </div>

                                <div class="row mt-3">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="first_name">First Name</label>
                                        <input
                                            id="first_name"
                                            v-model="form.first_name"
                                            type="text"
                                            class="form-control"
                                            placeholder="First Name"
                                            required
                                        >
                                        <FieldError :message="form.errors.first_name" />
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="last_name">Last Name</label>
                                        <input
                                            id="last_name"
                                            v-model="form.last_name"
                                            type="text"
                                            class="form-control"
                                            placeholder="Last Name"
                                            required
                                        >
                                        <FieldError :message="form.errors.last_name" />
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="email">Email</label>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="form-control"
                                        placeholder="Email"
                                        required
                                    >
                                    <FieldError :message="form.errors.email" />
                                </div>

                                <template v-if="isEdit">
                                    <label class="form-label d-block">Reset Password</label>
                                    <button
                                        type="button"
                                        class="btn btn-xs btn-toggle"
                                        :class="{ active: resetPassword }"
                                        :aria-pressed="resetPassword"
                                        @click="resetPassword = !resetPassword"
                                    >
                                        <div class="handle"></div>
                                    </button>
                                </template>

                                <div v-if="resetPassword" class="card-body reset-pass-section px-0 py-1">
                                    <div class="mb-3">
                                        <label class="form-label" for="password">
                                            New password:
                                            <a
                                                class="bs-tooltip"
                                                href="javascript:void(0)"
                                                data-bs-placement="right"
                                                title="A minimum of 8 characters, and should contain at least 1 uppercase, 1 number, 1 special character."
                                            ><i class="fa-solid fa-circle-exclamation"></i></a>
                                        </label>
                                        <input
                                            id="password"
                                            v-model="form.password"
                                            type="password"
                                            class="form-control"
                                            required
                                        >
                                        <FieldError :message="form.errors.password" />
                                    </div>

                                    <div v-if="isEdit" class="mb-3">
                                        <label class="form-label" for="password_confirmation">Verify password:</label>
                                        <input
                                            id="password_confirmation"
                                            v-model="form.password_confirmation"
                                            type="password"
                                            class="form-control"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="phone">Phone</label>
                                    <PhoneInput id="phone" v-model="form.phone" required />
                                    <FieldError :message="form.errors.phone" />
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="zipcode">Zip Code</label>
                                    <input
                                        id="zipcode"
                                        v-model="form.zipcode"
                                        type="text"
                                        class="form-control"
                                        placeholder="zipcode"
                                        required
                                    >
                                    <FieldError :message="form.errors.zipcode" />
                                </div>
                            </div>

                            <div class="card-footer text-center">
                                <button type="submit" class="btn btn-sm btn-primary" :disabled="form.processing">
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
