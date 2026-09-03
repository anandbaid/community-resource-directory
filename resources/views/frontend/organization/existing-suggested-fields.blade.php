<div class="col-xl-3 col-lg-4 aos-init aos-animate" data-aos="fade-up">
    <div class="row existing-organization-logo">
        <div class="text-center">
            <div class="org-logo-box">
                @if (isset($organization->logo))
                    <img id="organization_logo_output" alt="" src="{{ asset($organization->logo) }}"
                        class="img-responsive" width="" height="">
                @endif
                <div>
                    <input type="file" class="d-none" name="organization_logo" id="organization_logo" />
                    <div id="uploadFile">
                        <div id="uploadFileBtn">
                            <!-- <p>Logo/image here</p> -->
                            <button type="button" class="border-upload-btn">Upload
                                Image<i class="fas fa-upload border-upload-icon-btn"></i>
                            </button>
                            <input type="hidden" name="organization_logo_prev" value="{{ $organization->logo ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-9 col-lg-8" data-aos="fade-up">

    <div class="row gy-3 mt-0 ">
        <input type="hidden" class="form-control" id="organization_name" name="organization_name"
            value="{{ $organization->name }}" placeholder="Organization name">
        <div class="col-md-12">
            <select name="organization_type" id="organization_type" class="form-select" required>
                <option value="">Organization Type</option>
                <option value="government" {{ $organization->type == 'government' ? 'selected' : '' }}>
                    Government</option>
                <option value="non-government" {{ $organization->type == 'non-government' ? 'selected' : '' }}>
                    Non-Government</option>
            </select>
        </div>
        <div class="col-md-6">
            <input type="email" class="form-control" id="email" name="email"
                value="{{ $organization->email ?? '' }}" placeholder="Email" required>
        </div>
        <div class="col-md-6">
            <input type="tel" class="form-control phone-mask" id="phone" name="phone"
                value="{{ $organization->phone ?? '' }}" placeholder="Phone Number" required>
        </div>
        <div class="col-md-12">
            <input type="url" class="form-control" id="website" name="website"
                value="{{ $organization->website ?? '' }}" placeholder="Website/ URL" required>
        </div>
    </div>
    <!-- social media start -->
    <div class="my-3 col-md-12 social-area">
        <div class="frm-left-label">
            <h6>Social Media:</h6>
        </div>
        @php
            $social_links = json_decode($organizationDetails->social_links, true);
        @endphp
        <div class="social-input-container g-3 row">
            <div class="social-input-box col-md-4">
                <i class="fa-brands fa-facebook-f social-input-icon"></i>
                <input type="url" class="form-control" id="facebook" name="facebook"
                    value="{{ $social_links['facebook'] ?? '' }}" placeholder="Facebook" required>
            </div>
            <div class="social-input-box col-md-4">
                <i class="fa-brands fa-linkedin-in social-input-icon"></i>
                <input type="url" class="form-control" id="linkedin" name="linkedin"
                    value="{{ $social_links['linkedin'] ?? '' }}" placeholder="Linkedin" required>
            </div>
            <div class="social-input-box col-md-4">
                <i class="fa-brands fa-instagram social-input-icon"></i>
                <input type="url" class="form-control" id="instagram" name="instagram"
                    value="{{ $social_links['instagram'] ?? '' }}" placeholder="Instagram" required>
            </div>
        </div>
    </div>

    <div class="row mt-3 gx-3">
        <div>
            <div class="textarea-wrapper">
                <textarea name="service_description" id="service_description" cols="30" rows="4" maxlength="350"
                    placeholder="Program/Service Description" required>{{ $organizationDetails->service_description ?? '' }}</textarea>
                <span class="textarea-note">(350-character limit)</span>
            </div>
        </div>
        <div class="my-3 col-md-12">
            <h6 class="mb-3">Service Categories:</h6>
            <div class="row gy-3 gx-3 checkbox-container">
                @foreach ($categories as $category)
                    <div class="col-md-4">
                        <label class="check-label">
                            <input type="checkbox" name="service_categories[]" value="{{ $category->id }}"
                                {{ in_array($category->id, json_decode($organization->category ?? '', true) ?? []) ? 'checked' : '' }}
                                class="custom-checkbox" required>
                            {{ $category->name }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="mb-3 col-md-12 service-area">
            <div class="frm-left-label">
                <h6>Service Area(s)</h6>
                <!-- <div class="sub-inp-text">Press CTRL to multi-select</div> -->
            </div>
            <div class="radio-container gap-3">
                <label>
                    <input type="radio" name="service_area" value="local"
                        {{ $organization->service_area_type == 'local' ? 'checked' : '' }} required>
                    Local
                </label>
                <label>
                    <input type="radio" name="service_area" value="state"
                        {{ $organization->service_area_type == 'state' ? 'checked' : '' }} required>
                    State
                </label>
                <label>
                    <input type="radio" name="service_area" value="national"
                        {{ $organization->service_area_type == 'national' ? 'checked' : '' }} required>
                    National
                </label>
            </div>
        </div>
        @if ($organization->service_area_type == 'state')
            <div class="mb-3 dynamic-field">
                <div class="frm-left-label"></div>
                <div class="col-md-4">
                    <select name="service_state" id="service_state" class="form-select" required>
                        <option value="">Select State</option>
                        @foreach ($states as $state)
                            <option value="{{ $state->name }}"
                                {{ $organization->service_area == $state->name ? 'selected' : '' }}>
                                {{ $state->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
        <div class="col-md-12 target-area">
            <div class="frm-left-label">
                <h6>Target Population:</h6>
            </div>
            <div class="radio-container gap-3">
                <label>
                    <input type="radio" name="target_population" required value="Adult"
                        {{ $organization->target_population == 'Adult' ? 'checked' : '' }}>
                    Adult
                </label>
                <label>
                    <input type="radio" name="target_population" required value="Youth"
                        {{ $organization->target_population == 'Youth' ? 'checked' : '' }}>
                    Youth
                </label>
                <label>
                    <input type="radio" name="target_population" required value="Justice Impacted"
                        {{ $organization->target_population == 'Justice Impacted' ? 'checked' : '' }}>
                    Justice Impacted
                </label>
            </div>
        </div>
        <div class="my-5 frm-divider"></div>

    </div>
    <div class="row gx-3 gy-3">
        <div class="col-md-12">
            <h6>Point Of Contact</h6>
        </div>
        <div class="col-md-2">
            <select name="point_of_contact_pronouns" id="pronouns" class="form-select">
                <option value="">
                    Pronouns</option>
                <option value="He/Him" {{ $pointofcontacts->pronouns == 'He/Him' ? 'selected' : '' }}>
                    He/Him</option>
                <option value="She/Her" {{ $pointofcontacts->pronouns == 'She/Her' ? 'selected' : '' }}>
                    She/Her</option>
                <option value="They/Them" {{ $pointofcontacts->pronouns == 'They/Them' ? 'selected' : '' }}>
                    They/Them</option>
            </select>
        </div>
        <div class="col-md-5">
            <input type="text" class="form-control" id="first_name" name="point_of_contact_first_name"
                value="{{ $pointofcontacts->first_name ?? '' }}" placeholder="First Name">
        </div>
        <div class="col-md-5">
            <input type="text" class="form-control" id="last_name" name="point_of_contact_last_name"
                value="{{ $pointofcontacts->last_name ?? '' }}" placeholder="Last Name">
        </div>
        <div class="col-md-6">
            <input type="tel" class="form-control phone-mask" id="point_of_contact_phone"
                name="point_of_contact_phone"
                value="{{ $pointofcontacts->phone ?? '' }}" placeholder="Phone Number">
        </div>
        <div class="col-md-6">
            <input type="email" class="form-control" id="point_of_contact_email" name="point_of_contact_email"
                value="{{ $pointofcontacts->email ?? '' }}" placeholder="Email">
        </div>
        <div class="col-md-12">
            <div class="textarea-wrapper">
                <textarea name="point_of_contact_notes" id="notes" cols="30" rows="4" placeholder="Notes"
                    maxlength="250">{{ $pointofcontacts->notes ?? '' }}</textarea>
                <span class="textarea-note">(250-character limit)</span>
            </div>
        </div>
    </div>

    <div class="my-5 frm-divider"></div>

    <div class="row gx-3 gy-3">
        <div class="col-md-12">
            <h6>Physical Address</h6>
        </div>
        <div class="col-md-12">
            <input type="text" class="form-control" id="address_1" name="address_1"
                value="{{ $organizationDetails->physical_address_1 ?? '' }}" placeholder="Address Line 1" required>

            {{-- address --}}
            <input type="hidden" id="latitude" name="latitude" value="{{ $organizationDetails->latitude ?? '' }}">
            <input type="hidden" id="longitude" name="longitude" value="{{ $organizationDetails->longitude ?? '' }}">
            {{-- address end --}}
        </div>
        <div class="col-md-12">
            <input type="text" class="form-control" id="address_2" name="address_2"
                value="{{ $organizationDetails->physical_address_2 ?? '' }}" placeholder="Address Line 2">
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" id="city" name="city"
                value="{{ $organizationDetails->physical_city ?? '' }}" placeholder="City" required>
        </div>
        <div class="col-md-4">
            <select name="state" id="state" class="form-select" required>
                <option value="">State</option>
                @foreach ($states as $state)
                    <option value="{{ $state->name }}"
                        {{ $organizationDetails->physical_state == $state->name ? 'selected' : '' }}>
                        {{ $state->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" id="postcode" name="postcode"
                value="{{ $organizationDetails->physical_postal_code ?? '' }}" placeholder="Postcode" required>
        </div>
    </div>

    <div class="my-5 frm-divider"></div>

    <div class="row gx-3 gy-3">
        <div class="col-md-12 mb-3">
            <h6>Mailing Address <span class="sub-inp-text">(if available)</span>
            </h6>
        </div>
        <div class="col-md-12">
            <input type="text" class="form-control" id="mailing_address_1" name="mailing_address_1"
                value="{{ $organizationDetails->mailing_address_1 ?? '' }}" placeholder="Address Line 1">
        </div>
        <div class="col-md-12">
            <input type="text" class="form-control" id="mailing_address_2" name="mailing_address_2"
                value="{{ $organizationDetails->mailing_address_2 ?? '' }}" placeholder="Address Line 2">
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" id="mailing_city" name="mailing_city"
                value="{{ $organizationDetails->mailing_city ?? '' }}" placeholder="City">
        </div>
        <div class="col-md-4">
            <select name="mailing_state" id="mailing_state" class="form-select">
                <option value="">State</option>
                @foreach ($states as $state)
                    <option value="{{ $state->name }}"
                        {{ $organizationDetails->mailing_state == $state->name ? 'selected' : '' }}>
                        {{ $state->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" id="mailing_postcode" name="mailing_postcode"
                value="{{ $organizationDetails->mailing_postal_code ?? '' }}" placeholder="Postcode">
        </div>
    </div>

    <div class="my-5 frm-divider"></div>

    <div class="row gx-3 gy-3 publication-container">
        <div class="col-md-12 custFlexBetween">
            <h6>ADDITIONAL RESOURCES/PUBLICATIONS</h6>
            <button type="button" id="add-publication" class="blueLink add-pub-btn">+Add
                More</button>
        </div>

        @foreach ($publications ?? [] as $key => $publication)
            <div class="publication-item">
                <div class="col-md-12 mb-3">
                    <div class="d-flex justify-content-end">
                        <button type="button" class="remove-ico remove-publication"><i
                                class="fa-solid fa-trash"></i></button>
                    </div>
                    <input type="text" class="form-control publication-title" name="publication_title[{{ $key }}]"
                        value="{{ $publication->title }}" placeholder="Title">
                    <input type="hidden" class="publication-update-existing"
                        name="publication_update_existing[{{ $key }}]" value="1">
                </div>
                <div class="col-md-12 mb-3">
                    <select name="publication_state[{{ $key }}]" class="form-select publication-states">
                        <option value="">Select State</option>
                        <option value="national" {{ $publication->state === 'national' ? 'selected' : '' }}>National</option>
                        @foreach ($states as $state)
                            <option value="{{ $state->id }}"
                                {{ (string) $publication->state === (string) $state->id ? 'selected' : '' }}>
                                {{ $state->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <div class="textarea-wrapper">
                        <textarea name="publication_description[{{ $key }}]" cols="30" rows="4" maxlength="250"
                            placeholder="Description">{{ $publication->description }}</textarea>
                        <span class="textarea-note">(250-character limit)</span>
                    </div>
                </div>
                <div class="form-group col-md-12 mb-3">
                    <label class="form-label gap-3">File URL:
                        <div class="cover_file_container">
                            <input type="file" class="d-none cover-file"
                                name="cover_file[{{ $key }}]" />
                            <button type="button" class="cover-file-btn">Choose File<i
                                    class="fas fa-upload"></i></button>
                            <input type="hidden" name="cover_file_path[{{ $key }}]"
                                value="{{ $publication->file }}">
                            <div>
                                <span
                                    class="file-name-display">{{ !empty($publication->file) ? url($publication->file) : '' }}</span>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="form-group col-md-12 mb-3">
                    <label class="form-label gap-3">Cover Image:
                        <div class="cover_file_container">
                            <input type="file" class="d-none cover-image" name="cover_image[{{ $key }}]" />
                            <button type="button" class="cover-image-btn">Choose Image<i
                                    class="fas fa-upload"></i></button>
                            <input type="hidden" name="cover_image_path[{{ $key }}]"
                                value="{{ $publication->image }}">
                            <div>
                                <span
                                    class="file-name-display">{{ !empty($publication->image) ? url($publication->image) : '' }}</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row justify-content-end" data-aos="fade-up">
        <div class="col-xl-12">
            <input type="submit" class="primary-btn mt-3 d-flex mx-auto save-btn" value="Submit">
        </div>
    </div>

</div>
