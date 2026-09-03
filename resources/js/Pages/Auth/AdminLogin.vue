<script setup>
import { useForm } from '@inertiajs/vue3';

import FieldError from '@/Components/FieldError.vue';
import PasswordInput from '@/Components/PasswordInput.vue';

const props = defineProps({
    submitUrl: { type: String, required: true },
    // Already reduced to a same-site destination by the controller.
    redirect: { type: String, default: '' },
});

const form = useForm({
    administrator_email: '',
    administrator_password: '',
    redirect: props.redirect,
});

function submit() {
    form.post(props.submitUrl, {
        onFinish: () => form.reset('administrator_password'),
    });
}
</script>

<template>
    <div class="app-content d-flex justify-content-center align-items-center h-100">
        <div class="container-fluid w-100">
            <div class="row">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100 maxw-5">
                    <div class="d-table-cell align-middle">
                        <div class="text-center mt-4">
                            <h1 class="h2">Login</h1>
                            <p class="lead">Login with admin credentials to continue</p>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <form @submit.prevent="submit">
                                        <div class="mb-3">
                                            <label class="form-label" for="administrator_email">Email</label>
                                            <input
                                                id="administrator_email"
                                                v-model="form.administrator_email"
                                                class="form-control form-control-lg"
                                                type="email"
                                                autocomplete="email"
                                                placeholder="Enter your email"
                                                required
                                            >
                                            <FieldError :message="form.errors.administrator_email" />
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <PasswordInput
                                                v-model="form.administrator_password"
                                                placeholder="Enter your password"
                                            />
                                            <FieldError :message="form.errors.administrator_password" />
                                        </div>

                                        <div class="d-grid gap-2 mt-3">
                                            <button type="submit" class="btn btn-lg btn-primary" :disabled="form.processing">
                                                {{ form.processing ? 'Signing in…' : 'Sign in' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
