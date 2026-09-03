<script setup>
import PageHeader from '@/Components/Admin/PageHeader.vue';
import PhoneInput from '@/Components/PhoneInput.vue';

defineProps({
    user: { type: Object, required: true },
    avatarUrl: { type: String, required: true },
    indexUrl: { type: String, required: true },
});
</script>

<template>
    <PageHeader
        title="View User"
        :breadcrumbs="[{ label: 'Users', url: indexUrl }, { label: 'View User' }]"
    />

    <div class="app-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mt-2 mb-0 text-center">View User Details</h4>
                        </div>

                        <div class="card-body px-3">
                            <div class="text-center">
                                <img
                                    :src="avatarUrl"
                                    alt=""
                                    class="img-responsive mt-2"
                                    width="120"
                                    height="120"
                                >
                            </div>

                            <div class="row mt-3">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" :value="user.first_name" disabled>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" :value="user.last_name" disabled>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" :value="user.email" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <PhoneInput :model-value="user.phone" disabled />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Zip Code</label>
                                <input type="text" class="form-control" :value="user.zipcode" disabled>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            <a :href="indexUrl" class="btn btn-sm btn-primary">Back to users</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
