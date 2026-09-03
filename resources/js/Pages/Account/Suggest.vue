<script setup>
import { onMounted, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

import FieldError from '@/Components/FieldError.vue';
import PhoneInput from '@/Components/PhoneInput.vue';
import PublicationRows from '@/Components/Publications/PublicationRows.vue';
import { attachAutocomplete, matchState } from '@/lib/googleMaps';
import { withRecaptcha } from '@/lib/recaptcha';

const props = defineProps({
    type: { type: String, required: true },
    submitUrl: { type: String, required: true },
    fieldsUrl: { type: String, required: true },
    checkTitlesUrl: { type: String, required: true },
    mapApiKey: { type: String, default: '' },
    placeholderImage: { type: String, required: true },
    states: { type: Array, default: () => [] },
    publicationStates: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    organizations: { type: Array, default: () => [] },
    values: { type: Object, required: true },
});

const form = useForm({
    suggestion_type: props.type,
    ...props.values,
    organization_logo: null,
    recaptcha_token: null,
});

const addressInput = ref(null);
const mailingAddressInput = ref(null);
const logoInput = ref(null);
const logoPreview = ref(props.values.logoUrl || props.placeholderImage);
const loadingFields = ref(false);
const duplicateTitles = ref([]);
const mapError = ref('');

const isExisting = () => form.suggestion_type === 'existing';

onMounted(() => {
    attachAutocomplete(addressInput.value, applyPhysicalPlace, props.mapApiKey)
        .catch((error) => { mapError.value = error.message; });
    attachAutocomplete(mailingAddressInput.value, applyMailingPlace, props.mapApiKey)
        .catch(() => {});
});

// Switching between "new" and "existing" resets the form to the matching shape:
// blank for a new organization, prefilled once one is picked.
watch(() => form.suggestion_type, (type) => {
    duplicateTitles.value = [];

    if (type === 'new') {
        loadFields(null);
    } else {
        applyValues({ ...props.values, existing_organization_value: '' });
    }
});

watch(() => form.existing_organization_value, (id) => {
    if (isExisting() && id) {
        loadFields(id);
    }
});

watch(() => form.service_area, (value) => {
    if (value !== 'state') {
        form.service_state = '';
    }
});

async function loadFields(organizationId) {
    loadingFields.value = true;

    try {
        const { data } = await window.axios.get(props.fieldsUrl, {
            params: organizationId ? { organization: organizationId } : {},
        });

        applyValues({
            ...data.values,
            existing_organization_value: organizationId ?? '',
        });
    } finally {
        loadingFields.value = false;
    }
}

function applyValues(values) {
    Object.entries(values).forEach(([key, value]) => {
        form[key] = value;
    });

    form.organization_logo = null;
    logoPreview.value = values.logoUrl || props.placeholderImage;
}

function applyPhysicalPlace(place) {
    if (!place?.hasGeometry) {
        return;
    }

    form.latitude = place.latitude;
    form.longitude = place.longitude;

    if (place.city) form.city = place.city;
    if (place.postalCode) form.postcode = place.postalCode;

    const state = matchState(props.states, place.stateLong, place.stateShort);

    if (state) form.state = state;
}

function applyMailingPlace(place) {
    if (!place) {
        return;
    }

    if (place.formattedAddress) form.mailing_address_1 = place.formattedAddress;
    if (place.city) form.mailing_city = place.city;
    if (place.postalCode) form.mailing_postcode = place.postalCode;

    const state = matchState(props.states, place.stateLong, place.stateShort);

    if (state) form.mailing_state = state;
}

function onLogoChange(event) {
    const file = event.target.files[0] ?? null;

    form.organization_logo = file;
    logoPreview.value = file
        ? URL.createObjectURL(file)
        : form.logoUrl || props.placeholderImage;
}

/**
 * Ask the server which publication titles already exist so rows can offer the
 * "update the existing one instead" opt-in before the submit is rejected.
 */
async function refreshDuplicateTitles() {
    const titles = form.publications
        .map((row) => String(row.publication_title || '').trim())
        .filter(Boolean);

    if (titles.length === 0 || isExisting()) {
        duplicateTitles.value = [];

        return;
    }

    try {
        const { data } = await window.axios.post(
            props.checkTitlesUrl,
            { titles },
            { headers: { 'X-CSRF-TOKEN': window.csrf_token } },
        );

        duplicateTitles.value = (data.duplicates ?? []).map((row) => row.title.toLowerCase());
    } catch (error) {
        duplicateTitles.value = [];
    }
}

async function submit() {
    await refreshDuplicateTitles();

    // Flatten the publication rows into the indexed field names the controller
    // reads (publication_title[0], cover_file[0], ...).
    form.transform((data) => {
        const { publications, logoUrl, ...rest } = data;
        const flat = { ...rest };

        publications.forEach((row, index) => {
            flat[`publication_title[${index}]`] = row.publication_title;
            flat[`publication_description[${index}]`] = row.publication_description;
            flat[`publication_state[${index}]`] = row.publication_state;
            flat[`publication_update_existing[${index}]`] = row.publication_update_existing ? 1 : 0;
            flat[`cover_file_path[${index}]`] = row.cover_file_path ?? '';
            flat[`cover_image_path[${index}]`] = row.cover_image_path ?? '';

            if (row.cover_file) flat[`cover_file[${index}]`] = row.cover_file;
            if (row.cover_image) flat[`cover_image[${index}]`] = row.cover_image;
        });

        return flat;
    });

    withRecaptcha(
        form,
        () => form.post(props.submitUrl, { forceFormData: true, preserveScroll: true }),
        'suggest_resource',
    );
}
</script>

<template>
    <main>
        <section>
            <div class="container panel">
                <div class="text-center mb-50">
                    <h3 data-aos="fade-up" class="text-center admin-head">Suggest A Resource</h3>
                </div>

                <div class="white-box resource-frm-container">
                    <form class="suggest_resource" autocomplete="off" @submit.prevent="submit">
                        <FieldError :message="form.errors.captcha" />

                        <div class="card-body">
                            <div class="row gx-4">
                                <div class="col-12" data-aos="fade-up">
                                    <div class="row gy-3">
                                        <div class="col-md-12 organization-area">
                                            <div class="radio-container gap-3">
                                                <label>
                                                    <input v-model="form.suggestion_type" type="radio" value="new" required>
                                                    New Organization
                                                </label>
                                                <label>
                                                    <input v-model="form.suggestion_type" type="radio" value="existing" required>
                                                    Edit To An Existing Organization
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="isExisting()" class="row gy-3 mt-0">
                                        <div class="col-md-12">
                                            <select v-model="form.existing_organization_value" class="form-select" required>
                                                <option value="">Select Existing Organization</option>
                                                <option
                                                    v-for="organization in organizations"
                                                    :key="organization.id"
                                                    :value="organization.id"
                                                >{{ organization.name }}</option>
                                            </select>
                                            <FieldError :message="form.errors.existing_organization_value" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p v-if="loadingFields" class="mt-3 mb-0 text-muted">Loading organization details…</p>

                            <div v-show="!isExisting() || form.existing_organization_value" class="row gx-4 mt-3">
                                <div class="col-xl-3 col-lg-4" data-aos="fade-up">
                                    <div class="text-center">
                                        <img :src="logoPreview" alt="" class="img-responsive mt-2" width="140" height="140">
                                        <div class="mt-2">
                                            <input
                                                ref="logoInput"
                                                type="file"
                                                class="d-none"
                                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                                @change="onLogoChange"
                                            >
                                            <button type="button" class="secondary-btn" @click="logoInput.click()">
                                                <i class="fas fa-upload"></i> Upload Logo
                                            </button>
                                        </div>
                                        <FieldError :message="form.errors.organization_logo" />
                                    </div>
                                </div>

                                <div class="col-xl-9 col-lg-8" data-aos="fade-up">
                                    <div class="row gy-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Organization Name</label>
                                            <input v-model="form.organization_name" type="text" class="form-control" placeholder="Organization name" required>
                                            <FieldError :message="form.errors.organization_name" />
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label">Organization Type</label>
                                            <select v-model="form.organization_type" class="form-select" required>
                                                <option value="">Select Type</option>
                                                <option value="government">Government</option>
                                                <option value="non-government">Non-Government</option>
                                            </select>
                                            <FieldError :message="form.errors.organization_type" />
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input v-model="form.email" type="email" class="form-control" placeholder="Email" required>
                                            <FieldError :message="form.errors.email" />
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Phone</label>
                                            <PhoneInput v-model="form.phone" required />
                                            <FieldError :message="form.errors.phone" />
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label">Website</label>
                                            <input v-model="form.website" type="url" class="form-control" placeholder="Website" required>
                                            <FieldError :message="form.errors.website" />
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Facebook</label>
                                            <input v-model="form.facebook" type="url" class="form-control" placeholder="Facebook">
                                            <FieldError :message="form.errors.facebook" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Linkedin</label>
                                            <input v-model="form.linkedin" type="url" class="form-control" placeholder="Linkedin">
                                            <FieldError :message="form.errors.linkedin" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Instagram</label>
                                            <input v-model="form.instagram" type="url" class="form-control" placeholder="Instagram">
                                            <FieldError :message="form.errors.instagram" />
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label">Program/Service Description</label>
                                            <div class="textarea-wrapper">
                                                <textarea v-model="form.service_description" cols="30" rows="3" maxlength="350" required></textarea>
                                                <span class="textarea-note">(350-character limit)</span>
                                            </div>
                                            <FieldError :message="form.errors.service_description" />
                                        </div>

                                        <div class="col-md-12">
                                            <h5>Service Categories</h5>
                                            <div class="row checkbox-container">
                                                <div v-for="category in categories" :key="category.id" class="col-md-4">
                                                    <label class="check-label">
                                                        <input
                                                            v-model="form.service_categories"
                                                            type="checkbox"
                                                            :value="category.id"
                                                            class="custom-checkbox"
                                                        >{{ category.name }}
                                                    </label>
                                                </div>
                                            </div>
                                            <FieldError :message="form.errors.service_categories" />
                                        </div>

                                        <div class="col-md-12">
                                            <h5>Service Areas</h5>
                                            <div class="radio-container gap-3">
                                                <label><input v-model="form.service_area" type="radio" value="local" required> Local</label>
                                                <label><input v-model="form.service_area" type="radio" value="state" required> State</label>
                                                <label><input v-model="form.service_area" type="radio" value="national" required> National</label>
                                            </div>
                                            <FieldError :message="form.errors.service_area" />
                                        </div>

                                        <div v-if="form.service_area === 'state'" class="col-md-4">
                                            <label class="form-label">State</label>
                                            <select v-model="form.service_state" class="form-select" required>
                                                <option value="">Select State</option>
                                                <option v-for="state in states" :key="state" :value="state">{{ state }}</option>
                                            </select>
                                            <FieldError :message="form.errors.service_state" />
                                        </div>

                                        <div class="col-md-12">
                                            <h5>Target Population</h5>
                                            <div class="radio-container gap-3">
                                                <label><input v-model="form.target_population" type="radio" value="Adult" required> Adult</label>
                                                <label><input v-model="form.target_population" type="radio" value="Youth" required> Youth</label>
                                                <label><input v-model="form.target_population" type="radio" value="Justice Impacted" required> Justice Impacted</label>
                                            </div>
                                            <FieldError :message="form.errors.target_population" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <h5>Point Of Contact</h5>
                                    <div class="row gy-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Pronouns</label>
                                            <select v-model="form.point_of_contact_pronouns" class="form-select">
                                                <option value="He/Him">He/Him</option>
                                                <option value="She/Her">She/Her</option>
                                                <option value="They/Them">They/Them</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">First Name</label>
                                            <input v-model="form.point_of_contact_first_name" type="text" class="form-control" placeholder="First Name">
                                            <FieldError :message="form.errors.point_of_contact_first_name" />
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Last Name</label>
                                            <input v-model="form.point_of_contact_last_name" type="text" class="form-control" placeholder="Last Name">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input v-model="form.point_of_contact_email" type="email" class="form-control" placeholder="Email">
                                            <FieldError :message="form.errors.point_of_contact_email" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone</label>
                                            <PhoneInput v-model="form.point_of_contact_phone" />
                                            <FieldError :message="form.errors.point_of_contact_phone" />
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Notes</label>
                                            <div class="textarea-wrapper">
                                                <textarea v-model="form.point_of_contact_notes" cols="30" rows="3" maxlength="250"></textarea>
                                                <span class="textarea-note">(250-character limit)</span>
                                            </div>
                                            <FieldError :message="form.errors.point_of_contact_notes" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <h5>Physical Address</h5>
                                    <div class="row gy-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Address Line 1</label>
                                            <input
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
                                        <div class="col-md-12">
                                            <label class="form-label">Address Line 2</label>
                                            <input v-model="form.address_2" type="text" class="form-control" placeholder="Address Line 2">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">City</label>
                                            <input v-model="form.city" type="text" class="form-control" placeholder="City" required>
                                            <FieldError :message="form.errors.city" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">State</label>
                                            <select v-model="form.state" class="form-select" required>
                                                <option value="">Select State</option>
                                                <option v-for="state in states" :key="state" :value="state">{{ state }}</option>
                                            </select>
                                            <FieldError :message="form.errors.state" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Postal Code</label>
                                            <input v-model="form.postcode" type="text" class="form-control" placeholder="Postcode" required>
                                            <FieldError :message="form.errors.postcode" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <h5>Mailing Address</h5>
                                    <div class="row gy-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Address Line 1</label>
                                            <input
                                                ref="mailingAddressInput"
                                                v-model="form.mailing_address_1"
                                                type="text"
                                                class="form-control"
                                                placeholder="Address Line 1"
                                                autocomplete="off"
                                            >
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Address Line 2</label>
                                            <input v-model="form.mailing_address_2" type="text" class="form-control" placeholder="Address Line 2">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">City</label>
                                            <input v-model="form.mailing_city" type="text" class="form-control" placeholder="City">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">State</label>
                                            <select v-model="form.mailing_state" class="form-select">
                                                <option value="">Select State</option>
                                                <option v-for="state in states" :key="state" :value="state">{{ state }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Postal Code</label>
                                            <input v-model="form.mailing_postcode" type="text" class="form-control" placeholder="Postcode">
                                            <FieldError :message="form.errors.mailing_postcode" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <h5>Publications</h5>
                                    <FieldError :message="form.errors.publication_title" />
                                    <PublicationRows
                                        v-model="form.publications"
                                        :publication-states="publicationStates"
                                        :duplicate-titles="duplicateTitles"
                                        :errors="form.errors"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="primary-btn" :disabled="form.processing">
                                {{ form.processing ? 'Submitting…' : 'Submit' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
</template>
