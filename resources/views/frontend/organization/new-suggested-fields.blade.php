<div class="col-xl-3 col-lg-4 aos-init aos-animate" data-aos="fade-up">
    <div class="row new-organization-logo">
        <div class="text-center">
            <div class="org-logo-box">
                <img id="organization_logo_output" alt="" src="" class="img-responsive" width=""
                    height="">
                <div>
                    <input type="file" class="d-none" name="organization_logo" id="organization_logo" />
                    <div id="uploadFile">
                        <div id="uploadFileBtn">
                            <!-- <p>Logo/image here</p> -->
                            <button type="button" class="border-upload-btn">Upload
                                Image<i class="fas fa-upload border-upload-icon-btn"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-xl-9 col-lg-8" data-aos="fade-up">
    <div class="row gy-3 mt-0 ">
        <div class="col-md-12">
            <input type="text" class="form-control" id="organization_name" name="organization_name" value=""
                placeholder="Organization name" required>
        </div>
        <div class="col-md-12">
            <select name="organization_type" id="organization_type" class="form-select" required>
                <option value="">Organization Type</option>
                <option value="government">
                    Government</option>
                <option value="non-government">
                    Non-Government</option>
            </select>
        </div>
        <div class="col-md-6">
            <input type="email" class="form-control" id="email" name="email" value="" placeholder="Email"
                required>
        </div>
        <div class="col-md-6">
            <input type="tel" class="form-control phone-mask" id="phone" name="phone"
                value="" placeholder="Phone Number" required>
        </div>
        <div class="col-md-12">
            <input type="url" class="form-control" id="website" name="website" value=""
                placeholder="Website/ URL" required>
        </div>
    </div>
    <!-- social media start -->
    <div class="my-3 col-md-12 social-area">
        <div class="frm-left-label">
            <h6>Social Media:</h6>
        </div>
        <div class="social-input-container g-3 row">
            <div class="social-input-box col-md-4">
                <i class="fa-brands fa-facebook-f social-input-icon"></i>
                <input type="url" class="form-control" id="facebook" name="facebook" value=""
                    placeholder="Facebook" required>
            </div>
            <div class="social-input-box col-md-4">
                <i class="fa-brands fa-linkedin-in social-input-icon"></i>
                <input type="url" class="form-control" id="linkedin" name="linkedin" value=""
                    placeholder="Linkedin" required>
            </div>
            <div class="social-input-box col-md-4">
                <i class="fa-brands fa-instagram social-input-icon"></i>
                <input type="url" class="form-control" id="instagram" name="instagram" value=""
                    placeholder="Instagram" required>
            </div>
        </div>
    </div>

    <div class="row mt-3 gx-3">
        <div>
            <div class="textarea-wrapper">
                <textarea name="service_description" id="service_description" cols="30" rows="4" maxlength="350"
                    placeholder="Program/Service Description" required></textarea>
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
                                class="custom-checkbox">
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
                    <input type="radio" name="service_area" value="local" required>
                    Local
                </label>
                <label>
                    <input type="radio" name="service_area" value="state" required>
                    State
                </label>
                <label>
                    <input type="radio" name="service_area" value="national" required>
                    National
                </label>
            </div>
        </div>
        <div class="col-md-12 target-area">
            <div class="frm-left-label">
                <h6>Target Population:</h6>
            </div>
            <div class="radio-container gap-3">
                <label>
                    <input type="radio" name="target_population" required value="Adult">
                    Adult
                </label>
                <label>
                    <input type="radio" name="target_population" required value="Youth">
                    Youth
                </label>
                <label>
                    <input type="radio" name="target_population" required value="Justice Impacted">
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
                <option value="He/Him">
                    He/Him</option>
                <option value="She/Her">
                    She/Her</option>
                <option value="They/Them">
                    They/Them</option>
            </select>
        </div>
        <div class="col-md-5">
            <input type="text" class="form-control" id="first_name" name="point_of_contact_first_name"
                value="" placeholder="First Name">
        </div>
        <div class="col-md-5">
            <input type="text" class="form-control" id="last_name" name="point_of_contact_last_name"
                value="" placeholder="Last Name">
        </div>
        <div class="col-md-6">
            <input type="tel" class="form-control phone-mask" id="point_of_contact_phone" name="point_of_contact_phone"
                value="" placeholder="Phone Number">
        </div>
        <div class="col-md-6">
            <input type="email" class="form-control" id="point_of_contact_email" name="point_of_contact_email"
                value="" placeholder="Email">
        </div>
        <div class="col-md-12">
            <div class="textarea-wrapper">
                <textarea name="point_of_contact_notes" id="notes" cols="30" rows="4" placeholder="Notes"
                    maxlength="250"></textarea>
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
            <input type="text" class="form-control" id="address_1" name="address_1" value=""
                placeholder="Address Line 1" required autocomplete="off">
            {{-- address --}}
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">
            {{-- address end --}}
        </div>
        <div class="col-md-12">
            <input type="text" class="form-control" id="address_2" name="address_2" value=""
                placeholder="Address Line 2">
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" id="city" name="city" value=""
                placeholder="City" required>
        </div>
        <div class="col-md-4">
            <select name="state" id="state" class="form-select" required>
                <option value="">State</option>
                @foreach ($states as $state)
                    <option value="{{ $state->name }}"> {{ $state->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" id="postcode" name="postcode" value=""
                placeholder="Postcode" required>
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
                value="" placeholder="Address Line 1">
        </div>
        <div class="col-md-12">
            <input type="text" class="form-control" id="mailing_address_2" name="mailing_address_2"
                value="" placeholder="Address Line 2">
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" id="mailing_city" name="mailing_city" value=""
                placeholder="City">
        </div>
        <div class="col-md-4">
            <select name="mailing_state" id="mailing_state" class="form-select">
                <option value="">State</option>
                @foreach ($states as $state)
                    <option value="{{ $state->name }}"> {{ $state->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" id="mailing_postcode" name="mailing_postcode" value=""
                placeholder="Postcode">
        </div>
    </div>

    <div class="my-5 frm-divider"></div>

    <div class="row gx-3 gy-3 publication-container">
        <div class="col-md-12 custFlexBetween">
            <h6>ADDITIONAL RESOURCES/PUBLICATIONS</h6>
            <button type="button" id="add-publication" class="blueLink add-pub-btn">+Add
                More</button>
        </div>
        <div class="publication-item">
            <div class="col-md-12 mb-3">
                <div class="d-flex justify-content-end">
                    <button type="button" class="remove-ico remove-publication"><i
                            class="fa-solid fa-trash"></i></button>
                </div>
                <input type="text" class="form-control publication-title" name="publication_title[0]" value=""
                    placeholder="Title">
                <input type="hidden" class="publication-update-existing" name="publication_update_existing[0]"
                    value="0">
            </div>
            <div class="col-md-12 mb-3">
                <select name="publication_state[0]" class="form-select publication-states">
                    <option value="">Select State</option>
                    <option value="national">National</option>
                    @foreach ($states as $state)
                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 mb-3">
                <div class="textarea-wrapper">
                    <textarea name="publication_description[0]" cols="30" rows="4" maxlength="250" placeholder="Description"></textarea>
                    <span class="textarea-note">(250-character limit)</span>
                </div>
            </div>
            <div class="form-group col-md-12 mb-3">
                <label class="form-label gap-3">File URL:
                    <div class="cover_file_container">
                        <input type="file" class="d-none cover-file" name="cover_file[0]" />
                        <button type="button" class="cover-file-btn">Choose File<i
                                class="fas fa-upload"></i></button>
                        <div>
                            <span class="file-name-display"></span>
                        </div>
                    </div>
                </label>
            </div>
            <div class="form-group col-md-12 mb-3">
                <label class="form-label gap-3">Cover Image:
                    <div class="cover_file_container">
                        <input type="file" class="d-none cover-image" name="cover_image[0]" />
                        <button type="button" class="cover-image-btn">Choose Image<i
                                class="fas fa-upload"></i></button>
                        <div>
                            <span class="file-name-display"></span>
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <div class="row justify-content-end" data-aos="fade-up">
        <div class="col-xl-12">
            <input type="submit" class="primary-btn mt-3 d-flex mx-auto save-btn" value="Submit">
        </div>
    </div>

</div>
