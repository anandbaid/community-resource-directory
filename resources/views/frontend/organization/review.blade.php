@extends('frontend.layouts.app')
@section('title', ' | Review Organization')
@section('light-back', 'light-back')
@section('content')
    <main>
        <!-- search result start -->
        <section>
            <div class="container panel">
                <div class="text-center mb-50">
                    <h3 data-aos="fade-up" class="text-center admin-head">
                        Agency/Organization Rating
                    </h3>
                </div>

                <div class="white-box resource-frm-container">
                    <h3 class="mb-lg-5 mb-4">{{ $organization->name }}</h3>
                    <form action="{{ url('submit-review') }}" name="review_rating" method="post"
                        class="suggest_resource feedback-frm">
                        @csrf
                        <ol>
                            <input type="hidden" name="organization_id" value="{{ $organization->id }}">
                            <li>
                                <h5>In which state, district, or territory do you currently reside</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <select id="states" name="states" required>
                                            <option value="" Selected>Select State</option>
                                            @foreach ($states as $state)
                                                <option value="{{ $state->name }}">{{ $state->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <h5>
                                    Are you a system impacted individual?
                                </h5>
                                <div class="radio-container gap-3">
                                    <label>
                                        <input type="radio" name="system_impacted" class="system-impacted" value="yes"
                                            required>
                                        Yes
                                    </label>
                                    <label>
                                        <input type="radio" name="system_impacted" class="system-impacted" value="no"
                                            required>
                                        No
                                    </label>
                                </div>

                                <div class="checkbox-container conditional-container d-none">
                                    <h5>
                                        please select the option below that best describes your connection to the criminal
                                        legal system.
                                    </h5>
                                    <div class="row gy-3 gx-3 form-legal-section">

                                    </div>
                                </div>
                            </li>
                            <li>
                                <h5>
                                    Are you currently serving a term of supervision (e.g. probation, parole, supervised
                                    release, etc.)?
                                </h5>
                                <div class="radio-container gap-3">
                                    <label>
                                        <input type="radio" name="term_of_supervision" class="term-of-supervision"
                                            value="yes" required>
                                        Yes
                                    </label>
                                    <label>
                                        <input type="radio" name="term_of_supervision" class="term-of-supervision"
                                            value="no" required>
                                        No
                                    </label>
                                </div>
                            </li>
                            <li>
                                <h5>
                                    Is your rating based on personal experience or third-party disclosure?
                                </h5>
                                <div class="radio-container gap-3">
                                    <label>
                                        <input type="radio" name="experience" value="Personal Experience" required>
                                        Personal Experience
                                    </label>
                                    <label>
                                        <input type="radio" name="experience" value="Third-Party Disclosure" required>
                                        Third-Party Disclosure
                                    </label>
                                </div>
                            </li>
                            <li>
                                <h5>
                                    On what date was your initial interaction with the agency/organization you're
                                    currently rating? If unsure, please provide as close an estimate as possible.
                                </h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="date" class="form-control" id="initial_interaction"
                                            name="initial_interaction" value="" required>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <h5>
                                    Did/does your involvement with this agency/organization include structured (i.e.
                                    in-person, virtual, or hybrid) programming or require your participation in
                                    structured classes (e.g. career readiness, victim impact, parenting, etc.)?
                                </h5>
                                <div class="radio-container gap-3">
                                    <label>
                                        <input type="radio" name="structured_involvement" class="structured-involvement"
                                            value="yes" required>
                                        Yes
                                    </label>
                                    <label>
                                        <input type="radio" name="structured_involvement" class="structured-involvement"
                                            value="no" required>
                                        No
                                    </label>
                                </div>

                                <div class="checkbox-container conditional-container d-none">
                                    <h5>
                                        Were you required to attend a minimum number of program sessions and/or actively
                                        engage in classroom activities to successfully complete enrollment requirements?
                                    </h5>
                                    <div class="row gy-3 gx-3 form-structured-involvement">

                                    </div>
                                </div>
                            </li>
                            <li>
                                <h5>
                                    Was/is your involvement with this agency/organization mandated by the courts and/or
                                    probation/parole?
                                </h5>
                                <div class="radio-container gap-3">
                                    <label>
                                        <input type="radio" name="mandated_by_the_courts" value="yes" required>
                                        Yes
                                    </label>
                                    <label>
                                        <input type="radio" name="mandated_by_the_courts" value="no" required>
                                        No
                                    </label>
                                </div>
                            </li>
                            <li>
                                <h5>
                                    Did you find the agency/organization details (e g. Name, Address, Description,
                                    Service Categories, etc.) provided to be accurate?
                                </h5>
                                <div class="radio-container gap-3">
                                    <label>
                                        <input type="radio" name="accurate_details" class="accurate-details"
                                            value="yes" required>
                                        Yes
                                    </label>
                                    <label>
                                        <input type="radio" name="accurate_details" class="accurate-details"
                                            value="no" required>
                                        No
                                    </label>
                                </div>

                                <div class="checkbox-container conditional-container d-none">
                                    <h5>
                                        Briefly describe any details provided in their listing that you determined to be
                                        inaccurate or misleading during your agency/organization interaction.
                                    </h5>
                                    <div class="row gy-3 gx-3 form-accurate-details">

                                    </div>
                                </div>
                            </li>
                            <li>
                                <h5>
                                    Based on your personal experience with this agency/organization, would you recommend
                                    them to others?
                                </h5>
                                <div class="radio-container gap-3">
                                    <label>
                                        <input type="radio" name="recommend" value="yes" required>
                                        Yes
                                    </label>
                                    <label>
                                        <input type="radio" name="recommend" value="no" required>
                                        No
                                    </label>
                                </div>
                            </li>
                            <li>
                                <h5>
                                    Using the options below, select the number (1-5, 1 = Least Likely to Recommend, 5 =
                                    Most Likely to Recommend) that best reflects your experience with this
                                    agency/organization.
                                </h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <select id="rating" name="rating" required>
                                            <option value="" Selected>Select Option</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                        </select>
                                    </div>
                                </div>
                            </li>
                        </ol>
                        <input type="submit" class="primary-btn mt-5 d-flex mx-auto save-btn" value="Submit">
                    </form>

                    @php
                        $legal_systems = [
                            'Returning Citizen',
                            'Family or Friend',
                            'Concerned Citizen',
                            'Program Staff',
                            'Legal Representative',
                            'Educator',
                            'Other',
                        ];
                    @endphp
                    <div class="legal-section d-none">
                        @foreach ($legal_systems as $legal_system)
                            <div class="col-md-3">
                                <label class="check-label">
                                    <input type="checkbox" name="legal_system[]" value="{{ $legal_system }}"
                                        class="custom-checkbox" required>{{ $legal_system }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="structured-involvement-section d-none">
                        <div class="radio-container gap-3">
                            <label>
                                <input type="radio" name="classroom_activities" value="yes" required>
                                Yes
                            </label>
                            <label>
                                <input type="radio" name="classroom_activities" value="no" required>
                                No
                            </label>
                        </div>
                    </div>

                    <div class="accurate-details-section d-none">
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="details" name="details" value=""
                                required>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('custom-scripts')
    <script type="text/javascript">
        window.addEventListener('load', function() {
            $("#states").select2({
                minimumResultsForSearch: Infinity
            });
            $("#rating").select2({
                minimumResultsForSearch: Infinity
            });

            $('.system-impacted').on('change', function(e) {
                if ($(this).is(':checked')) {
                    if ($(this).val() == 'yes') {
                        $(this).closest('li').find('.form-legal-section').empty().html($(
                            '.legal-section').html())
                        $(this).closest('li').find('.conditional-container').removeClass('d-none');
                    } else {
                        $(this).closest('li').find('.form-legal-section').empty()
                        $(this).closest('li').find('.conditional-container').addClass('d-none');
                    }
                }
            })

            $('.structured-involvement').on('change', function(e) {
                if ($(this).is(':checked')) {
                    if ($(this).val() == 'yes') {
                        $(this).closest('li').find('.form-structured-involvement').empty().html($(
                            '.structured-involvement-section').html())
                        $(this).closest('li').find('.conditional-container').removeClass('d-none');
                    } else {
                        $(this).closest('li').find('.form-structured-involvement').empty()
                        $(this).closest('li').find('.conditional-container').addClass('d-none');
                    }
                }
            })

            $('.accurate-details').on('change', function(e) {
                if ($(this).is(':checked')) {
                    if ($(this).val() == 'no') {
                        $(this).closest('li').find('.form-accurate-details').empty().html($(
                            '.accurate-details-section').html())
                        $(this).closest('li').find('.conditional-container').removeClass('d-none');
                    } else {
                        $(this).closest('li').find('.form-accurate-details').empty()
                        $(this).closest('li').find('.conditional-container').addClass('d-none');
                    }
                }
            })

            $(document).on('click', '.save-btn', function(e) {
                e.preventDefault();
                const button = $(this);
                button.prop('disabled', true);
                let validator = validateForm($('form[name="review_rating"]'), {}, {})
                let url = $('form[name="review_rating"]').attr('action')
                let method = $('form[name="review_rating"]').attr('method')
                if (validator.form()) {
                    let formElement = $('form[name="review_rating"]')[0];
                    let formData = new FormData(formElement);
                    console.log(formData)
                    $.ajax({
                        url: url,
                        method: method,
                        data: formData,
                        processData: false, // Prevent jQuery from processing the data
                        contentType: false, // Prevent jQuery from setting content type
                        success: function(response) {
                            // console.log(response)
                            swalAlert2(response.status, response.message, response.redirect)
                            setTimeout(function() {
                                window.location.href = response.redirect
                            }, 1500);
                        },
                        error: function(response) {
                            swalAlert(response.responseJSON.status, response.responseJSON
                                .errors, 7000)
                            button.prop('disabled', false);
                        },
                        complete: function() {
                            button.prop('disabled', false);
                        }
                    });
                } else {
                    button.prop('disabled', false);
                }
            })
        })
    </script>
@endpush
