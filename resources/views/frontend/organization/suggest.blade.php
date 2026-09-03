@extends('frontend.layouts.app')
@section('title', ' | Suggest A Resource')
@section('light-back', 'light-back')
@section('content')
    <main>
        <!-- search result start -->
        <section>
            <div class="container panel">
                <div class="text-center mb-50">
                    <h3 data-aos="fade-up" class="text-center admin-head">
                        Suggest A Resource
                    </h3>
                </div>

                <div class="white-box resource-frm-container">

                    <form name="suggestOrganization" id="suggestOrganization" class="suggest_resource"
                        action="{{ route('submit-suggestions') }}" method="POST" enctype="multipart/form-data"
                        autocomplete="off">
                        <div class="card-body">
                            <input type="hidden" name="redirect" value="{{ $redirect }}">
                            <div class="row gx-4">
                                <!-- image uploader start -->
                                <div class="col-xl-3 col-lg-4" data-aos="fade-up">

                                </div>
                                <div class="col-xl-9 col-lg-8" data-aos="fade-up" id="suggest_org_section_left">
                                    <div class="row gy-3">
                                        <div class="col-md-12 organization-area">
                                            <div class="radio-container gap-3">
                                                <label>
                                                    <input type="radio" name="suggestion_type" value="new" required
                                                        {{ $type == 'new' ? 'checked' : '' }}>
                                                    New Organization
                                                </label>
                                                <label>
                                                    <input type="radio" name="suggestion_type" value="existing"
                                                        {{ $type == 'existing' ? 'checked' : '' }} required>
                                                    Edit To An Existing Organization
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="existing-organization-fields">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="organization-fields row mt-lg-0 mt-3">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <div class="publication-item clone-publication d-none">
            <hr class="hr-line">
            <div class="mb-3 col-md-12">
                <div class="d-flex justify-content-end">
                    <button type="button" class="remove-ico remove-publication"><i class="fa-solid fa-trash"></i></button>
                </div>
                <input type="text" class="form-control publication-title" name="publication_title[]"
                    data-name="publication_title" value="" placeholder="Title">
                <input type="hidden" class="publication-update-existing" name="publication_update_existing[]"
                    data-name="publication_update_existing" value="0">
            </div>
            <div class="mb-3 col-md-12">
                <select name="publication_state[]" class="form-select publication-states" data-name="publication_state"
                    >
                    <option value="">Select State</option>
                    <option value="national">National</option>
                    @foreach ($states as $state)
                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3 col-md-12">
                <div class="textarea-wrapper">
                    <textarea name="publication_description[]" cols="30" rows="4" data-name="publication_description"
                        maxlength="250" placeholder="Description"></textarea>
                    <span class="textarea-note">(250-character limit)</span>
                </div>
            </div>
            <div class="form-group col-md-12 mb-3">
                <label class="form-label gap-3">File URL:
                    <div class="cover_file_container">
                        <input type="file" class="d-none cover-file" name="cover_file[]" data-name="cover_file"
                            />
                        <button type="button" class="cover-file-btn">Choose File<i class="fas fa-upload"></i></button>
                        <input type="hidden" name="cover_file_path[]" data-name="cover_file_path" value="">
                        <div>
                            <span class="file-name-display"></span>
                        </div>
                    </div>
                </label>
            </div>
            <div class="form-group col-md-12 mb-3">
                <label class="form-label gap-3">Cover Image:
                    <div class="cover_file_container">
                        <input type="file" class="d-none cover-image" name="cover_image[]" data-name="cover_image"
                            />
                        <button type="button" class="cover-image-btn">Choose Image<i class="fas fa-upload"></i></button>
                        <input type="hidden" name="cover_image_path[]" data-name="cover_image_path" value="">
                        <div>
                            <span class="file-name-display"></span>
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </main>
@endsection
@push('custom-scripts')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('custom.map_api_key') }}&libraries=places"></script>
    <!-- Include Google Maps Places API -->
    <script>
        function setIfExists(id, value) {
            var el = document.getElementById(id);
            if (el && value) {
                el.value = value;
            }
        }

        function setStateValue(stateValue, stateShort) {
            setStateValueFor('state', stateValue, stateShort);
        }

        function setStateValueFor(selectId, stateValue, stateShort) {
            var select = document.getElementById(selectId);
            if (!select) {
                return;
            }
            var candidates = [];
            if (stateValue) {
                candidates.push(stateValue);
            }
            if (stateShort && stateShort !== stateValue) {
                candidates.push(stateShort);
            }
            if (candidates.length === 0) {
                return;
            }
            var options = Array.prototype.slice.call(select.options);
            var match = options.find(function(option) {
                return candidates.includes(option.value) || candidates.includes(option.text);
            });
            if (match) {
                select.value = match.value;
                if (window.jQuery) {
                    var $select = window.jQuery(select);
                    if ($select.data('select2')) {
                        $select.val(match.value).trigger('change.select2');
                    } else {
                        $select.val(match.value).trigger('change');
                    }
                }
            }
        }

        function handlePlaceSelection(place) {
            if (!place || !place.geometry || !place.geometry.location) {
                return;
            }
            setIfExists('latitude', place.geometry.location.lat());
            setIfExists('longitude', place.geometry.location.lng());

            var components = place.address_components || [];
            var findComponent = function(type) {
                return components.find(function(component) {
                    return component.types.includes(type);
                });
            };

            var cityComponent = findComponent('locality') || findComponent('sublocality') || findComponent(
                'administrative_area_level_2');
            var stateComponent = findComponent('administrative_area_level_1');
            var postalComponent = findComponent('postal_code');

            setIfExists('city', cityComponent ? cityComponent.long_name : '');
            setIfExists('postcode', postalComponent ? postalComponent.long_name : '');
            if (stateComponent) {
                setStateValue(stateComponent.long_name, stateComponent.short_name);
            }
        }

        function handleMailingPlaceSelection(place) {
            if (!place || !place.geometry) {
                return;
            }

            var components = place.address_components || [];
            var findComponent = function(type) {
                return components.find(function(component) {
                    return component.types.includes(type);
                });
            };

            var cityComponent = findComponent('locality') || findComponent('sublocality') || findComponent(
                'administrative_area_level_2');
            var stateComponent = findComponent('administrative_area_level_1');
            var postalComponent = findComponent('postal_code');

            setIfExists('mailing_address_1', place.formatted_address || place.name);
            setIfExists('mailing_city', cityComponent ? cityComponent.long_name : '');
            setIfExists('mailing_postcode', postalComponent ? postalComponent.long_name : '');
            if (stateComponent) {
                setStateValueFor('mailing_state', stateComponent.long_name, stateComponent.short_name);
            }
        }

        function initialize() {
            var input = document.getElementById('address_1');
            if (!input) {
                return;
            }
            var autocomplete = new google.maps.places.Autocomplete(input, {
                componentRestrictions: {
                    country: 'us'
                },
            });

            autocomplete.addListener('place_changed', function() {
                handlePlaceSelection(autocomplete.getPlace());
            });
        }

        function initializeMailing() {
            var input = document.getElementById('mailing_address_1');
            if (!input) {
                return;
            }
            var autocomplete = new google.maps.places.Autocomplete(input, {
                componentRestrictions: {
                    country: 'us'
                },
            });

            autocomplete.addListener('place_changed', function() {
                handleMailingPlaceSelection(autocomplete.getPlace());
            });
        }
    </script>
    <script type="text/javascript">
        window.addEventListener('load', function() {

            $(document).on('input', '.phone-mask', function() {
                applyPhoneMask(this);
            });

            $(document).on('click', 'input[name="service_area"]', function() {
                // Remove any existing dynamically added field
                $('.dynamic-field').remove();
                const formContainer = $('.service-area');
                if ($(this).val() === 'state') {
                    // Add the dynamic field if "state" is selected
                    const dynamicField = $(`
                        <div class="mb-3 dynamic-field">
                            <div class="frm-left-label"></div>
                            <div class="col-md-4">
                                <select name="service_state" id="service_state" class="form-select" required>
                                    <option value="">Select State</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state->name }}"> {{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    `);
                    formContainer.after(dynamicField);
                    $("#service_state").select2({
                        minimumResultsForSearch: Infinity
                    });
                }
            });

            $(document).on('change', '#existing_organization_value', function() {
                let organization = $(this).val();
                $.ajax({
                    url: "{{ url('/get-suggested-fields') }}",
                    data: {
                        organization: organization,
                    },
                    beforeSend: function() {
                        $("#ajax-loader").show();
                    },
                    success: function(result) {
                        $('#suggest_org_section_left').removeClass('w-100');
                        $('.organization-fields').html(result);
                        $("#service_state").select2({
                            minimumResultsForSearch: Infinity
                        });
                        select2js();
                        applyPhoneMaskToAll();
                        initialize();
                        initializeMailing();
                    },
                    error: function(response) {
                        $('#suggest_org_section_left').addClass('w-100');
                        $('.organization-fields').empty();
                    },
                    complete: function() {
                        setTimeout(() => {
                            $('#ajax-loader').hide();
                        }, 500)
                    }
                });
            });

            // Run on load
            toggleFields();
            initializeMailing();
            // Run on change
            $(document).on('change', 'input[name="suggestion_type"]', function() {
                $("#ajax-loader").show();
                toggleFields();
                initializeMailing();
                setTimeout(() => {
                    $('#ajax-loader').hide();
                }, 500)
            });

            // Form Validation
            let rules = {
                phone: {
                    required: true,
                    phoneNo: true
                },
                point_of_contact_phone: {
                    required: false,
                    phoneNo: true
                },
                point_of_contact_email: {
                    required: false,
                    email: true
                },
                postcode: {
                    required: true,
                    postalCode: true
                },
                mailing_postcode: {
                    required: false,
                    postalCode: true
                },
                facebook: {
                    required: false,
                    facebookUrl: true
                },
                linkedin: {
                    required: false,
                    linkedinUrl: true
                },
                instagram: {
                    required: false,
                    instagramUrl: true
                },
                twitter: {
                    required: false,
                    twitterUrl: true
                }
            }

            $(document).on('click', '.save-btn', function(e) {
                e.preventDefault();
                const button = $(this);
                button.prop('disabled', true);
                let validator = validateForm($('form[name="suggestOrganization"]'), rules, {})
                let url = $('form[name="suggestOrganization"]').attr('action')
                let method = $('form[name="suggestOrganization"]').attr('method')
                if (!validator.form()) {
                    button.prop('disabled', false);
                    return;
                }

                const suggestionType = $('input[name="suggestion_type"]:checked').val();
                resetPublicationUpdateFlags();

                if (suggestionType === 'new') {
                    const titles = collectPublicationTitles();
                    if (titles.length === 0) {
                        submitSuggestionForm(url, method, button);
                        return;
                    }
                    checkPublicationTitles(titles)
                        .done(function(response) {
                            const duplicates = response.duplicates || [];
                            if (duplicates.length === 0) {
                                submitSuggestionForm(url, method, button);
                                return;
                            }
                            const duplicateTitles = duplicates.map(function(item) {
                                return item.title;
                            });
                            Swal.fire({
                                title: 'Publication already exists',
                                html: 'These publication titles already exist:<br><strong>' +
                                    duplicateTitles.join(', ') +
                                    '</strong><br><br>Would you like to update the existing publication(s)?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Update Existing',
                                cancelButtonText: 'Change Title'
                            }).then((result) => {
                                console.log('first2')
                                if (result.value) {
                                    console.log('first')
                                    setPublicationUpdateFlags(duplicateTitles);
                                    submitSuggestionForm(url, method, button);
                                } else {
                                    button.prop('disabled', false);
                                }
                            });
                        })
                        .fail(function() {
                            swalAlert('error',
                                'Unable to verify publication titles. Please try again.',
                                7000);
                            button.prop('disabled', false);
                        });
                    return;
                }

                submitSuggestionForm(url, method, button);
            })
        })

        function select2js() {
            $("#selectState").select2({
                minimumResultsForSearch: Infinity
            });
            $("#selectCategory").select2({
                minimumResultsForSearch: Infinity
            });
            $("#organization_type").select2({
                minimumResultsForSearch: Infinity
            });
            $("#pronouns").select2({
                minimumResultsForSearch: Infinity
            });
            $("#state").select2({
                minimumResultsForSearch: Infinity
            });
            $("#mailing_state").select2({
                minimumResultsForSearch: Infinity
            });
            $(".publication-states").select2({
                minimumResultsForSearch: Infinity
            });
        }
        // New and existing organization field toggle
        function toggleFields() {
            if ($('input[name="suggestion_type"]:checked').val() === 'existing') {
                let eistingOrganization =
                    `<div class="row gy-3 mt-0">
                        <div class="col-md-12">
                            <select name="existing_organization_value" id="existing_organization_value"
                                class="form-select" required>
                                <option value="">Select Existing Organization</option>
                                @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}">
                                        {{ $organization->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>`;
                $('.existing-organization-fields').html(eistingOrganization);
                $("#existing_organization_value").select2({
                    minimumResultsForSearch: Infinity
                });
                $('#suggest_org_section_left').addClass('w-100');
                $('.organization-fields').empty();
            } else {
                $('.existing-organization-fields').empty();
                $('#suggest_org_section_left').removeClass('w-100');
                $("#ajax-loader").show();
                $.ajax({
                    url: "{{ url('/get-suggested-fields') }}",
                    success: function(result) {
                        $('.organization-fields').html(result);
                        select2js();
                        applyPhoneMaskToAll();
                        initialize();
                        initializeMailing();
                    },
                    complete: function() {
                        $("#ajax-loader").fadeOut(); // Hide loader when AJAX completes
                        initialize();
                        initializeMailing();
                    }
                });
            }
        }

        function submitSuggestionForm(url, method, button) {
            let formElement = $('form[name="suggestOrganization"]')[0];
            const phoneSnapshots = sanitizePhoneInputs();
            let formData = new FormData(formElement);
            phoneSnapshots.forEach(function(snapshot) {
                snapshot.el.val(snapshot.formatted);
            });
            $.ajax({
                url: url,
                method: method,
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#ajax-loader").fadeIn();
                },
                success: function(response) {
                    swalAlert2(response.status, response.message, response.redirect)
                    setTimeout(function() {
                        window.location.href = response.redirect
                    }, 1500);
                },
                error: function(response) {
                    console.log(response)
                    swalAlert(response.responseJSON.status, response.responseJSON.errors, 7000)
                    button.prop('disabled', false);
                },
                complete: function() {
                    $("#ajax-loader").fadeOut();
                    button.prop('disabled', false);
                }
            });
        }

        function collectPublicationTitles() {
            const titles = [];
            $('.publication-item').each(function() {
                const titleInput = $(this).find('input[name^="publication_title"]');
                const descriptionInput = $(this).find('textarea[name^="publication_description"]');
                const title = String(titleInput.val() || '').trim();
                const description = String(descriptionInput.val() || '').trim();
                if (title.length > 0 && description.length > 0) {
                    titles.push(title);
                }
            });
            return titles;
        }

        function resetPublicationUpdateFlags() {
            $('.publication-update-existing').val('0');
        }

        function setPublicationUpdateFlags(duplicateTitles) {
            const lowerDupes = duplicateTitles.map(function(title) {
                return String(title).toLowerCase();
            });
            $('.publication-item').each(function() {
                const titleInput = $(this).find('input[name^="publication_title"]');
                const updateInput = $(this).find('.publication-update-existing');
                const title = String(titleInput.val() || '').trim().toLowerCase();
                if (lowerDupes.includes(title)) {
                    updateInput.val('1');
                }
            });
        }

        function checkPublicationTitles(titles) {
            return $.ajax({
                url: "{{ route('check-publication-titles') }}",
                method: 'POST',
                data: {
                    titles: titles,
                    _token: window.csrf_token
                }
            });
        }

        $(document).on('click', '.cover-file-btn', function(e) {
            e.preventDefault();
            $(this).siblings('input.cover-file').click();
        });

        $(document).on('click', '.cover-image-btn', function(e) {
            e.preventDefault();
            $(this).siblings('input.cover-image').click();
        });

        $(document).on('change', 'input.cover-file, input.cover-image', function() {
            const file = this.files && this.files[0];
            const container = $(this).closest('.cover_file_container');
            const display = container.find('.file-name-display');
            if (file) {
                display.text(file.name);
            }
        });
    </script>
@endpush
