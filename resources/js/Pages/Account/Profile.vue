<script setup>
import { useForm } from '@inertiajs/vue3';

import AccountLayout from '@/Components/Frontend/AccountLayout.vue';
import FieldError from '@/Components/FieldError.vue';
import PhoneInput from '@/Components/PhoneInput.vue';

const props = defineProps({
    quickLinks: { type: Object, required: true },
    bannerImage: { type: String, required: true },
    dashboardUrl: { type: String, required: true },
    submitUrl: { type: String, required: true },
    user: { type: Object, required: true },
    counts: { type: Object, required: true },
    values: { type: Object, required: true },
});

const form = useForm({ ...props.values });

function submit() {
    form.post(props.submitUrl, { preserveScroll: true });
}
</script>

<template>
    <AccountLayout
        :quick-links="quickLinks"
        :banner-image="bannerImage"
        banner-title="Profile"
        banner-subtitle="Review your details and keep your information current."
    >
        <div class="row gy-4">
            <div class="col-lg-6" data-aos="fade-up">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <p class="text-uppercase text-muted small mb-1">Signed in as</p>
                                <h4 class="mb-0">{{ user.name }}</h4>
                                <p class="text-muted mb-0">{{ user.email }}</p>
                            </div>
                            <a :href="dashboardUrl" class="textLink text-decoration-none">Back to dashboard</a>
                        </div>

                        <div class="row gy-3">
                            <div class="col-sm-6">
                                <p class="text-muted small mb-1">First Name</p>
                                <p class="mb-0">{{ user.first_name || 'Not provided' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-1">Last Name</p>
                                <p class="mb-0">{{ user.last_name || 'Not provided' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-1">Phone</p>
                                <p class="mb-0">{{ user.phoneFormatted || 'Not provided' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-1">Zip Code</p>
                                <p class="mb-0">{{ user.zipcode || 'Not provided' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-1">Account Status</p>
                                <p class="mb-0 text-capitalize">{{ user.status }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-1">Member Since</p>
                                <p class="mb-0">{{ user.memberSince }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="mb-3">Edit Profile</h5>

                        <form @submit.prevent="submit">
                            <div class="row gy-3">
                                <div class="col-sm-6">
                                    <label class="text-muted small mb-1" for="first_name">First Name</label>
                                    <input
                                        id="first_name"
                                        v-model="form.first_name"
                                        type="text"
                                        class="form-control"
                                        required
                                    >
                                    <FieldError :message="form.errors.first_name" />
                                </div>

                                <div class="col-sm-6">
                                    <label class="text-muted small mb-1" for="last_name">Last Name</label>
                                    <input id="last_name" v-model="form.last_name" type="text" class="form-control">
                                    <FieldError :message="form.errors.last_name" />
                                </div>

                                <div class="col-sm-6">
                                    <label class="text-muted small mb-1" for="phone">Phone</label>
                                    <PhoneInput id="phone" v-model="form.phone" placeholder="Enter Phone No" />
                                    <FieldError :message="form.errors.phone" />
                                </div>

                                <div class="col-sm-6">
                                    <label class="text-muted small mb-1" for="zipcode">Zip Code</label>
                                    <input
                                        id="zipcode"
                                        v-model="form.zipcode"
                                        type="text"
                                        class="form-control"
                                        placeholder="Enter Zip Code"
                                    >
                                    <FieldError :message="form.errors.zipcode" />
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="primary-btn" :disabled="form.processing">
                                        {{ form.processing ? 'Saving…' : 'Update Profile' }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="mb-3">Account Snapshot</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span>Saved resources</span>
                                <strong>{{ counts.savedResources }}</strong>
                            </li>
                            <li class="d-flex justify-content-between align-items-center py-2">
                                <span>Saved searches</span>
                                <strong>{{ counts.savedSearches }}</strong>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AccountLayout>
</template>
