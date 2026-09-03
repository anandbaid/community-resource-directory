<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

import FieldError from '@/Components/FieldError.vue';
import PhoneInput from '@/Components/PhoneInput.vue';
import PublicationModal from '@/Components/Organizations/PublicationModal.vue';
import { attachAutocomplete, matchState } from '@/lib/googleMaps';

const props = defineProps({
    mode: {
        type: String,
        default: 'create',
        // `review` is the suggested-organization screen: the same fields, a
        // different publication editor and Accept/Reject instead of Save.
        validator: (value) => ['create', 'edit', 'review'].includes(value),
    },
    // A suggestion that has already been accepted or rejected is read-only.
    readonly: {
        type: Boolean,
        default: false,
    },
    // Extra request fields the host page needs on the same payload.
    extraValues: {
        type: Object,
        default: () => ({}),
    },
    // Applied on top of this component's own transform, so a host page can fold
    // in fields it owns (the review screen's publication rows) without having to
    // re-implement the submit.
    transform: {
        type: Function,
        default: null,
    },
    // Where the form posts. Edit spoofs PUT so file uploads still work.
    submitUrl: {
        type: String,
        required: true,
    },
    // Flat map of every request field, pre-filled by the controller.
    values: {
        type: Object,
        required: true,
    },
    states: {
        type: Array,
        default: () => [],
    },
    publicationStates: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    publications: {
        type: Array,
        default: () => [],
    },
    logoUrl: {
        type: String,
        default: '',
    },
    currentFileUrl: {
        type: String,
        default: '',
    },
    placeholderImage: {
        type: String,
        required: true,
    },
    mapApiKey: {
        type: String,
        default: '',
    },
    publicationStoreUrl: {
        type: String,
        required: true,
    },
    ckeditorUrl: {
        type: String,
        default: '',
    },
});

const isEdit = computed(() => props.mode === 'edit');
const isReview = computed(() => props.mode === 'review');

const form = useForm({
    ...(isEdit.value ? { _method: 'put' } : {}),
    ...props.values,
    ...props.extraValues,
    organization_logo: null,
    // A suggestion carries a link here; the admin form carries an upload.
    file_url: props.mode === 'review' ? (props.values.file_url ?? '') : null,
});

// The host page drives Accept/Reject through this form's own submit.
defineExpose({ form, submit });

const formEl = ref(null);
const addressInput = ref(null);
const mailingAddressInput = ref(null);
const logoInput = ref(null);
const fileInput = ref(null);

const logoPreview = ref(props.logoUrl || props.placeholderImage);
const publicationList = ref([...props.publications]);
const publicationModalOpen = ref(false);
const mapError = ref('');

// Only "state" service areas take an explicit state; local derives it from the
// physical address and national is fixed, exactly as the controller does.
watch(
    () => form.service_area,
    (value) => {
        if (value !== 'state') {
            form.service_state = '';
        }
    },
);

onMounted(() => {
    attachAutocomplete(addressInput.value, applyPhysicalPlace, props.mapApiKey).catch((error) => {
        mapError.value = error.message;
    });

    attachAutocomplete(mailingAddressInput.value, applyMailingPlace, props.mapApiKey).catch(() => {
        // The physical-address attach reports the same failure; do not double up.
    });
});

function applyPhysicalPlace(place) {
    if (!place?.hasGeometry) {
        return;
    }

    form.latitude = place.latitude;
    form.longitude = place.longitude;

    if (place.city) {
        form.city = place.city;
    }
    if (place.postalCode) {
        form.postcode = place.postalCode;
    }

    const state = matchState(props.states, place.stateLong, place.stateShort);

    if (state) {
        form.state = state;
    }
}

function applyMailingPlace(place) {
    if (!place) {
        return;
    }

    if (place.formattedAddress) {
        form.mailing_address_1 = place.formattedAddress;
    }
    if (place.city) {
        form.mailing_city = place.city;
    }
    if (place.postalCode) {
        form.mailing_postcode = place.postalCode;
    }

    const state = matchState(props.states, place.stateLong, place.stateShort);

    if (state) {
        form.mailing_state = state;
    }
}

function onLogoChange(event) {
    const file = event.target.files[0] ?? null;

    form.organization_logo = file;
    logoPreview.value = file ? URL.createObjectURL(file) : props.logoUrl || props.placeholderImage;
}

function onFileChange(event) {
    form.file_url = event.target.files[0] ?? null;
}

function onPublicationCreated(publication) {
    publicationList.value.push(publication);
    form.assign_publication.push(publication.id);
}

function submit() {
    // Keep the browser's native `required` prompts that the Blade form relied on.
    // A rejection skips them: an admin must be able to throw out a suggestion
    // whose fields are junk, which is exactly why it is being rejected.
    const skipValidation = isReview.value && form.form_type === 'rejected';

    if (!skipValidation && formEl.value && !formEl.value.reportValidity()) {
        return;
    }

    form
        .transform((data) => {
            const payload = { ...data, is_member: data.is_member ? 1 : 0 };

            return props.transform ? props.transform(payload) : payload;
        })
        .post(props.submitUrl, {
            forceFormData: true,
            preserveScroll: true,
        });
}
</script>

<template>
    <form ref="formEl" class="px-3" autocomplete="off" @submit.prevent="submit">
        <!--
            One `disabled` on the fieldset takes every control inside with it,
            and disabled controls are skipped by constraint validation, so a
            read-only suggestion cannot be edited or submitted.
        -->
        <fieldset
            :disabled="readonly"
            style="border: 0; padding: 0; margin: 0; min-width: 0;"
        >
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="row">
                        <div class="text-center">
                            <img
                                :src="logoPreview"
                                alt=""
                                class="img-responsive mt-2"
                                width="120"
                                height="120"
                            >
                            <div class="mt-2">
                                <input
                                    ref="logoInput"
                                    type="file"
                                    class="d-none"
                                    accept="image/jpeg,image/png,image/jpg,image/webp"
                                    @change="onLogoChange"
                                >
                                <button type="button" class="btn btn-sm btn-primary" @click="logoInput.click()">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                            </div>
                            <FieldError :message="form.errors.organization_logo" />
                        </div>
                    </div>

                    <!--
                        Admin create/edit assigns existing publications; the
                        suggestion review edits the rows the visitor submitted.
                    -->
                    <div class="row mt-3">
                        <slot name="publications">
                            <div class="accordion" id="assign-publication">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button
                                            class="form-select"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseOne"
                                            aria-expanded="true"
                                            aria-controls="collapseOne"
                                        >
                                            Assign Publication
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#assign-publication">
                                        <div class="accordion-body">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary"
                                                @click="publicationModalOpen = true"
                                            >
                                                Add New
                                            </button>
                                            <hr>
                                            <div class="publication-list">
                                                <div v-for="publication in publicationList" :key="publication.id">
                                                    <input
                                                        v-model="form.assign_publication"
                                                        type="checkbox"
                                                        class="assign-publication mx-2"
                                                        :value="publication.id"
                                                    >{{ publication.title }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </slot>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label class="form-label" for="organization_name">Organization Name</label>
                            <input
                                id="organization_name"
                                v-model="form.organization_name"
                                type="text"
                                class="form-control"
                                placeholder="Organization name"
                                required
                            >
                            <FieldError :message="form.errors.organization_name" />
                        </div>

                        <div class="mb-3 col-md-12">
                            <label class="form-label" for="organization_type">Organization Type</label>
                            <select
                                id="organization_type"
                                v-model="form.organization_type"
                                class="form-select"
                                required
                            >
                                <option value="">Select Type</option>
                                <option value="government">Government</option>
                                <option value="non-government">Non-Government</option>
                            </select>
                            <FieldError :message="form.errors.organization_type" />
                        </div>

                        <div class="mb-3 col-md-6">
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

                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="phone">Phone</label>
                            <PhoneInput id="phone" v-model="form.phone" required />
                            <FieldError :message="form.errors.phone" />
                        </div>

                        <div class="mb-3 col-md-12">
                            <label class="form-label" for="website">Website</label>
                            <input
                                id="website"
                                v-model="form.website"
                                type="url"
                                class="form-control"
                                placeholder="Website"
                                required
                            >
                            <FieldError :message="form.errors.website" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h6><u>Social Media</u></h6>
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="facebook">Facebook</label>
                            <input id="facebook" v-model="form.facebook" type="url" class="form-control" placeholder="Facebook">
                            <FieldError :message="form.errors.facebook" />
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="linkedin">Linkedin</label>
                            <input id="linkedin" v-model="form.linkedin" type="url" class="form-control" placeholder="Linkedin">
                            <FieldError :message="form.errors.linkedin" />
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="instagram">Instagram</label>
                            <input id="instagram" v-model="form.instagram" type="url" class="form-control" placeholder="Instagram">
                            <FieldError :message="form.errors.instagram" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label" for="service_description">Program/Service Description</label>
                            <textarea
                                id="service_description"
                                v-model="form.service_description"
                                cols="30"
                                rows="2"
                                class="form-control"
                                maxlength="350"
                                required
                            ></textarea>
                            <FieldError :message="form.errors.service_description" />
                        </div>

                        <div class="mb-3 col-md-12">
                            <label class="form-label d-block"><h6><u>Service Categories</u></h6></label>
                            <div class="row checkbox-container">
                                <div v-for="category in categories" :key="category.id" class="mb-1 col-md-3">
                                    <input
                                        v-model="form.service_categories"
                                        type="checkbox"
                                        :value="category.id"
                                        class="mx-1"
                                    >{{ category.name }}
                                </div>
                            </div>
                            <FieldError :message="form.errors.service_categories" />
                        </div>

                        <div class="mb-3 col-md-12 service-area">
                            <label class="form-label d-block"><h6><u>Service Areas</u></h6></label>
                            <div class="radio-container">
                                <input v-model="form.service_area" type="radio" value="local" required> Local
                                <input v-model="form.service_area" type="radio" value="state" required> State
                                <input v-model="form.service_area" type="radio" value="national" required> National
                            </div>
                            <FieldError :message="form.errors.service_area" />
                        </div>

                        <div v-if="form.service_area === 'state'" class="mb-3 col-md-4 dynamic-field">
                            <label class="form-label" for="service_state">State</label>
                            <select id="service_state" v-model="form.service_state" class="form-select" required>
                                <option value="">Select State</option>
                                <option v-for="state in states" :key="state" :value="state">{{ state }}</option>
                            </select>
                            <FieldError :message="form.errors.service_state" />
                        </div>

                        <div class="mb-3 col-md-12">
                            <label class="form-label d-block"><h6><u>Target Population</u></h6></label>
                            <div class="radio-container">
                                <input v-model="form.target_population" type="radio" value="Adult" required> Adult
                                <input v-model="form.target_population" type="radio" value="Youth" required> Youth
                                <input v-model="form.target_population" type="radio" value="Justice Impacted" required>
                                Justice Impacted
                            </div>
                            <FieldError :message="form.errors.target_population" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label class="form-label" for="additional_resource">Additional Resources</label>
                            <input
                                id="additional_resource"
                                v-model="form.additional_resource"
                                type="text"
                                class="form-control"
                                placeholder="Additional Resources"
                            >
                            <FieldError :message="form.errors.additional_resource" />
                        </div>

                        <div class="mb-3 col-md-12">
                            <label class="form-label" for="title">Title</label>
                            <input id="title" v-model="form.title" type="text" class="form-control" placeholder="Title">
                            <FieldError :message="form.errors.title" />
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="description">Description</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                cols="30"
                                rows="2"
                                class="form-control"
                                maxlength="250"
                            ></textarea>
                            <FieldError :message="form.errors.description" />
                        </div>

                        <!--
                            `file_url` is a file upload on the admin form but a
                            link the visitor typed on a suggestion, so the two
                            surfaces render (and validate) it differently.
                        -->
                        <div class="mb-3 col-md-12">
                            <label class="form-label" for="file_url">{{ isReview ? 'File Url' : 'File' }}</label>
                            <input
                                v-if="isReview"
                                id="file_url"
                                v-model="form.file_url"
                                type="url"
                                class="form-control"
                                placeholder="File Url"
                            >
                            <input v-else id="file_url" ref="fileInput" type="file" class="form-control" @change="onFileChange">
                            <div v-if="currentFileUrl && !isReview" class="mt-2">
                                <a :href="currentFileUrl" target="_blank">View current file</a>
                            </div>
                            <FieldError :message="form.errors.file_url" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h6><u>Point Of Contact</u></h6>
                        </div>
                        <div class="mb-3 col-md-2">
                            <label class="form-label" for="pronouns">Pronouns</label>
                            <select id="pronouns" v-model="form.point_of_contact_pronouns" class="form-select">
                                <option value="He/Him">He/Him</option>
                                <option value="She/Her">She/Her</option>
                                <option value="They/Them">They/Them</option>
                            </select>
                            <FieldError :message="form.errors.point_of_contact_pronouns" />
                        </div>
                        <div class="mb-3 col-md-5">
                            <label class="form-label" for="first_name">First Name</label>
                            <input
                                id="first_name"
                                v-model="form.point_of_contact_first_name"
                                type="text"
                                class="form-control"
                                placeholder="First Name"
                            >
                            <FieldError :message="form.errors.point_of_contact_first_name" />
                        </div>
                        <div class="mb-3 col-md-5">
                            <label class="form-label" for="last_name">Last Name</label>
                            <input
                                id="last_name"
                                v-model="form.point_of_contact_last_name"
                                type="text"
                                class="form-control"
                                placeholder="Last Name"
                            >
                            <FieldError :message="form.errors.point_of_contact_last_name" />
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="point_of_contact_email">Email</label>
                            <input
                                id="point_of_contact_email"
                                v-model="form.point_of_contact_email"
                                type="email"
                                class="form-control"
                                placeholder="Email"
                            >
                            <FieldError :message="form.errors.point_of_contact_email" />
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="point_of_contact_phone">Phone</label>
                            <PhoneInput id="point_of_contact_phone" v-model="form.point_of_contact_phone" />
                            <FieldError :message="form.errors.point_of_contact_phone" />
                        </div>
                        <div class="mb-3 col-md-12">
                            <label class="form-label" for="notes">Notes</label>
                            <textarea
                                id="notes"
                                v-model="form.point_of_contact_notes"
                                cols="30"
                                rows="2"
                                class="form-control"
                                maxlength="250"
                            ></textarea>
                            <FieldError :message="form.errors.point_of_contact_notes" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h6><u>Physical Address</u></h6>
                        </div>
                        <div class="mb-3 col-md-12">
                            <label class="form-label" for="address_1">Address Line 1</label>
                            <input
                                id="address_1"
                                ref="addressInput"
                                v-model="form.address_1"
                                type="text"
                                class="form-control"
                                placeholder="Address Line 1"
                                autocomplete="off"
                                required
                            >
                            <FieldError :message="form.errors.address_1" />
                            <FieldError :message="form.errors.latitude" />
                            <FieldError :message="form.errors.longitude" />
                            <div v-if="mapError" class="form-text text-danger">{{ mapError }}</div>
                        </div>
                        <div class="mb-3 col-md-12">
                            <label class="form-label" for="address_2">Address Line 2</label>
                            <input
                                id="address_2"
                                v-model="form.address_2"
                                type="text"
                                class="form-control"
                                placeholder="Address Line 2"
                            >
                            <FieldError :message="form.errors.address_2" />
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="city">City</label>
                            <input id="city" v-model="form.city" type="text" class="form-control" placeholder="City" required>
                            <FieldError :message="form.errors.city" />
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="state">State</label>
                            <select id="state" v-model="form.state" class="form-select" required>
                                <option value="">Select State</option>
                                <option v-for="state in states" :key="state" :value="state">{{ state }}</option>
                            </select>
                            <FieldError :message="form.errors.state" />
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="postcode">Postal Code</label>
                            <input
                                id="postcode"
                                v-model="form.postcode"
                                type="text"
                                class="form-control"
                                placeholder="Postcode"
                                required
                            >
                            <FieldError :message="form.errors.postcode" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h6><u>Mailing Address</u></h6>
                        </div>
                        <div class="mb-3 col-md-12">
                            <label class="form-label" for="mailing_address_1">Address Line 1</label>
                            <input
                                id="mailing_address_1"
                                ref="mailingAddressInput"
                                v-model="form.mailing_address_1"
                                type="text"
                                class="form-control"
                                placeholder="Address Line 1"
                                autocomplete="off"
                            >
                            <FieldError :message="form.errors.mailing_address_1" />
                        </div>
                        <div class="mb-3 col-md-12">
                            <label class="form-label" for="mailing_address_2">Address Line 2</label>
                            <input
                                id="mailing_address_2"
                                v-model="form.mailing_address_2"
                                type="text"
                                class="form-control"
                                placeholder="Address Line 2"
                            >
                            <FieldError :message="form.errors.mailing_address_2" />
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="mailing_city">City</label>
                            <input
                                id="mailing_city"
                                v-model="form.mailing_city"
                                type="text"
                                class="form-control"
                                placeholder="City"
                            >
                            <FieldError :message="form.errors.mailing_city" />
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="mailing_state">State</label>
                            <select id="mailing_state" v-model="form.mailing_state" class="form-select">
                                <option value="">Select State</option>
                                <option v-for="state in states" :key="state" :value="state">{{ state }}</option>
                            </select>
                            <FieldError :message="form.errors.mailing_state" />
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label" for="mailing_postcode">Postal Code</label>
                            <input
                                id="mailing_postcode"
                                v-model="form.mailing_postcode"
                                type="text"
                                class="form-control"
                                placeholder="Postcode"
                            >
                            <FieldError :message="form.errors.mailing_postcode" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-12">
                            <label class="form-label d-block"><h6><u>Source</u></h6></label>
                            <div class="radio-container">
                                <input v-model="form.source" type="radio" value="Website"> Website
                                <input v-model="form.source" type="radio" value="Search"> Search
                                <input v-model="form.source" type="radio" value="Referral"> Referral
                                <input v-model="form.source" type="radio" value="Other"> Other
                            </div>
                            <FieldError :message="form.errors.source" />
                        </div>
                    </div>

                    <div v-if="!isReview" class="row">
                        <div class="mb-3">
                            <label class="form-label" for="is_member">Is Member</label>
                            <input id="is_member" v-model="form.is_member" type="checkbox" class="mx-1">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </fieldset>

        <div class="card-footer text-center">
            <slot name="actions" :form="form">
                <button type="submit" class="btn btn-sm btn-primary save-btn" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Save' }}
                </button>
            </slot>
        </div>
    </form>

    <PublicationModal
        :open="publicationModalOpen"
        :states="publicationStates"
        :store-url="publicationStoreUrl"
        :placeholder-image="placeholderImage"
        :ckeditor-url="ckeditorUrl"
        @close="publicationModalOpen = false"
        @created="onPublicationCreated"
    />
</template>
