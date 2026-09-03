<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

import FieldError from '@/Components/FieldError.vue';
import { withRecaptcha } from '@/lib/recaptcha';

const props = defineProps({
    submitUrl: { type: String, required: true },
    organization: { type: Object, required: true },
    states: { type: Array, default: () => [] },
    legalSystems: { type: Array, default: () => [] },
});

const form = useForm({
    organization_id: props.organization.id,
    states: '',
    system_impacted: '',
    legal_system: [],
    term_of_supervision: '',
    experience: '',
    initial_interaction: '',
    structured_involvement: '',
    classroom_activities: '',
    mandated_by_the_courts: '',
    accurate_details: '',
    details: '',
    recommend: '',
    rating: '',
    recaptcha_token: null,
});

// Follow-up questions are only validated when their trigger answer is chosen,
// so clear them again when the answer changes back.
watch(() => form.system_impacted, (value) => {
    if (value !== 'yes') {
        form.legal_system = [];
    }
});

watch(() => form.structured_involvement, (value) => {
    if (value !== 'yes') {
        form.classroom_activities = '';
    }
});

watch(() => form.accurate_details, (value) => {
    if (value !== 'no') {
        form.details = '';
    }
});

function submit() {
    // v3 tokens expire in ~2 minutes, so mint one now rather than at page load.
    withRecaptcha(form, () => form.post(props.submitUrl, { preserveScroll: true }), 'submit_review');
}
</script>

<template>
    <main>
        <section>
            <div class="container panel bg-container panel-bottom">
                <div class="text-center mb-50">
                    <h3 data-aos="fade-up" class="text-center admin-head">Rate this Agency/Organization</h3>
                </div>

                <div class="white-box resource-frm-container" data-aos="fade-up">
                    <h3 class="mb-lg-5 mb-4">{{ organization.name }}</h3>

                    <form name="review_rating" @submit.prevent="submit">
                        <FieldError :message="form.errors.captcha" />

                        <ol>
                            <li>
                                <h5>In which state, district, or territory do you currently reside</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <select v-model="form.states" required>
                                            <option value="">Select State</option>
                                            <option v-for="state in states" :key="state" :value="state">{{ state }}</option>
                                        </select>
                                    </div>
                                </div>
                                <FieldError :message="form.errors.states" />
                            </li>

                            <li>
                                <h5>Are you a system impacted individual?</h5>
                                <div class="radio-container gap-3">
                                    <label><input v-model="form.system_impacted" type="radio" value="yes" required> Yes</label>
                                    <label><input v-model="form.system_impacted" type="radio" value="no" required> No</label>
                                </div>
                                <FieldError :message="form.errors.system_impacted" />

                                <div v-if="form.system_impacted === 'yes'" class="checkbox-container conditional-container">
                                    <h5>
                                        please select the option below that best describes your connection to the
                                        criminal legal system.
                                    </h5>
                                    <div class="row gy-3 gx-3">
                                        <div v-for="option in legalSystems" :key="option" class="col-md-3">
                                            <label class="check-label">
                                                <input
                                                    v-model="form.legal_system"
                                                    type="checkbox"
                                                    :value="option"
                                                    class="custom-checkbox"
                                                >{{ option }}
                                            </label>
                                        </div>
                                    </div>
                                    <FieldError :message="form.errors.legal_system" />
                                </div>
                            </li>

                            <li>
                                <h5>
                                    Are you currently serving a term of supervision (e.g. probation, parole, supervised
                                    release, etc.)?
                                </h5>
                                <div class="radio-container gap-3">
                                    <label><input v-model="form.term_of_supervision" type="radio" value="yes" required> Yes</label>
                                    <label><input v-model="form.term_of_supervision" type="radio" value="no" required> No</label>
                                </div>
                                <FieldError :message="form.errors.term_of_supervision" />
                            </li>

                            <li>
                                <h5>Is your rating based on personal experience or third-party disclosure?</h5>
                                <div class="radio-container gap-3">
                                    <label><input v-model="form.experience" type="radio" value="Personal Experience" required> Personal Experience</label>
                                    <label><input v-model="form.experience" type="radio" value="Third-Party Disclosure" required> Third-Party Disclosure</label>
                                </div>
                                <FieldError :message="form.errors.experience" />
                            </li>

                            <li>
                                <h5>
                                    On what date was your initial interaction with the agency/organization you're
                                    currently rating? If unsure, please provide as close an estimate as possible.
                                </h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input v-model="form.initial_interaction" type="date" class="form-control" required>
                                    </div>
                                </div>
                                <FieldError :message="form.errors.initial_interaction" />
                            </li>

                            <li>
                                <h5>
                                    Did/does your involvement with this agency/organization include structured (i.e.
                                    in-person, virtual, or hybrid) programming or require your participation in
                                    structured classes (e.g. career readiness, victim impact, parenting, etc.)?
                                </h5>
                                <div class="radio-container gap-3">
                                    <label><input v-model="form.structured_involvement" type="radio" value="yes" required> Yes</label>
                                    <label><input v-model="form.structured_involvement" type="radio" value="no" required> No</label>
                                </div>
                                <FieldError :message="form.errors.structured_involvement" />

                                <div v-if="form.structured_involvement === 'yes'" class="checkbox-container conditional-container">
                                    <h5>
                                        Were you required to attend a minimum number of program sessions and/or actively
                                        engage in classroom activities to successfully complete enrollment requirements?
                                    </h5>
                                    <div class="radio-container gap-3">
                                        <label><input v-model="form.classroom_activities" type="radio" value="yes"> Yes</label>
                                        <label><input v-model="form.classroom_activities" type="radio" value="no"> No</label>
                                    </div>
                                    <FieldError :message="form.errors.classroom_activities" />
                                </div>
                            </li>

                            <li>
                                <h5>
                                    Was/is your involvement with this agency/organization mandated by the courts and/or
                                    probation/parole?
                                </h5>
                                <div class="radio-container gap-3">
                                    <label><input v-model="form.mandated_by_the_courts" type="radio" value="yes" required> Yes</label>
                                    <label><input v-model="form.mandated_by_the_courts" type="radio" value="no" required> No</label>
                                </div>
                                <FieldError :message="form.errors.mandated_by_the_courts" />
                            </li>

                            <li>
                                <h5>
                                    Did you find the agency/organization details (e g. Name, Address, Description,
                                    Service Categories, etc.) provided to be accurate?
                                </h5>
                                <div class="radio-container gap-3">
                                    <label><input v-model="form.accurate_details" type="radio" value="yes" required> Yes</label>
                                    <label><input v-model="form.accurate_details" type="radio" value="no" required> No</label>
                                </div>
                                <FieldError :message="form.errors.accurate_details" />

                                <div v-if="form.accurate_details === 'no'" class="checkbox-container conditional-container">
                                    <h5>
                                        Briefly describe any details provided in their listing that you determined to be
                                        inaccurate or misleading during your agency/organization interaction.
                                    </h5>
                                    <div class="col-md-12">
                                        <input v-model="form.details" type="text" class="form-control">
                                    </div>
                                    <FieldError :message="form.errors.details" />
                                </div>
                            </li>

                            <li>
                                <h5>
                                    Based on your personal experience with this agency/organization, would you recommend
                                    them to others?
                                </h5>
                                <div class="radio-container gap-3">
                                    <label><input v-model="form.recommend" type="radio" value="yes" required> Yes</label>
                                    <label><input v-model="form.recommend" type="radio" value="no" required> No</label>
                                </div>
                                <FieldError :message="form.errors.recommend" />
                            </li>

                            <li>
                                <h5>
                                    Using the options below, select the number (1-5, 1 = Least Likely to Recommend, 5 =
                                    Most Likely to Recommend) that best reflects your experience with this
                                    agency/organization.
                                </h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <select v-model="form.rating" required>
                                            <option value="">Select Option</option>
                                            <option v-for="n in 5" :key="n" :value="String(n)">{{ n }}</option>
                                        </select>
                                    </div>
                                </div>
                                <FieldError :message="form.errors.rating" />
                            </li>
                        </ol>

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
