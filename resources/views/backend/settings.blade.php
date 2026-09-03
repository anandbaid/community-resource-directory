@extends('backend.layouts.app')
@section('title', ' | General Settings')
@section('content')

    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">General Settings</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            General Settings
                        </li>
                    </ol>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div> <!--end::App Content Header-->
    <!--begin::App Content-->
    <div class="app-content"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between">
                            <h5 class="card-title mb-0">General Settings</h5>
                        </div>
                        <div class="card-body">
                            <form name="settingsForm" id="settingsForm" class="px-3"
                                action="{{ route('admin.saveSettings') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Website Name: </label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" id="key[website_name]"
                                                name="key[website_name]" value="{{ $settingsArr['website_name'] ?? '' }}"
                                                placeholder="Website Name" required>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Site Title: </label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" id="key[site_title]"
                                                name="key[site_title]" value="{{ $settingsArr['site_title'] ?? '' }}"
                                                placeholder="Site Title" required>

                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Meta Title: </label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" id="key[meta_title]"
                                                name="key[meta_title]" value="{{ $settingsArr['meta_title'] ?? '' }}"
                                                placeholder="Meta Title" required>

                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Meta Keywords: </label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea id="key[meta_keywords]" name="key[meta_keywords]" class="form-control h-300" placeholder="Enter ..." required>{{ $settingsArr['meta_keywords'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Meta Descriptions: </label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea id="key[meta_description]" name="key[meta_description]" class="form-control h-300" placeholder="Enter ..."
                                                required>{{ $settingsArr['meta_description'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Copy right: </label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea id="key[copy_right]" name="key[copy_right]" class="form-control h-300 ck-editor-content"
                                                placeholder="Enter ..." required>{{ $settingsArr['copy_right'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Header Logo</label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="file" class="form-control" name="header_logo" accept="image/*">
                                            @php
                                                $headerLogo = $settingsArr['header_logo'] ?? '';
                                            @endphp
                                            <img src="{{ $headerLogo ? asset($headerLogo) : asset('assets/img/logo.png') }}"
                                                alt="Header Logo" class="mt-2" style="max-width: 200px; background-color: #d1d1d1; padding: 6px; border-radius: 6px;">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Footer Logo</label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="file" class="form-control" name="footer_logo" accept="image/*">
                                            @php
                                                $footerLogo = $settingsArr['footer_logo'] ?? '';
                                            @endphp
                                            <img src="{{ $footerLogo ? asset($footerLogo) : asset('assets/img/footer-logo.png') }}"
                                                alt="Footer Logo" class="mt-2" style="max-width: 200px; background-color: #d1d1d1; padding: 6px; border-radius: 6px;">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Footer Description</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea id="key[footer_description]" name="key[footer_description]" class="form-control h-300 ck-editor-content"
                                                placeholder="Enter ...">{{ $settingsArr['footer_description'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Footer Addresses</label>
                                        </div>
                                        <div class="col-md-10">
                                            <div class="mb-3">
                                                <label class="form-label">Headquarters Address</label>
                                                <textarea id="key[footer_hq_address]" name="key[footer_hq_address]" class="form-control h-120"
                                                    placeholder="Enter ...">{{ $settingsArr['footer_hq_address'] ?? '' }}</textarea>
                                            </div>
                                            @php
                                                $hqPhones = $settingsArr['footer_hq_phone'] ?? '[]';
                                                $hqPhones = is_array($hqPhones) ? $hqPhones : json_decode($hqPhones, true);
                                                $hqPhones = is_array($hqPhones) && count($hqPhones) ? $hqPhones : [''];
                                            @endphp
                                            <div class="mb-3">
                                                <label class="form-label">Headquarters Phones</label>
                                                <div id="footer-hq-phones">
                                                    @foreach ($hqPhones as $phone)
                                                        <div class="d-flex gap-2 mb-2">
                                                            <input type="text" class="form-control"
                                                                name="key[footer_hq_phone][]" value="{{ $phone }}"
                                                                placeholder="Enter ...">
                                                            <button type="button" class="btn btn-danger remove-field">Remove</button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button type="button" class="btn btn-secondary btn-sm add-more" data-target="#footer-hq-phones"
                                                    data-name="key[footer_hq_phone][]">Add Phone</button>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Headquarters Phone Hours (optional)</label>
                                                <input type="text" class="form-control" id="key[footer_hq_phone_hours]"
                                                    name="key[footer_hq_phone_hours]" value="{{ $settingsArr['footer_hq_phone_hours'] ?? '' }}"
                                                    placeholder="Enter ...">
                                            </div>
                                            @php
                                                $hqEmails = $settingsArr['footer_hq_email'] ?? '[]';
                                                $hqEmails = is_array($hqEmails) ? $hqEmails : json_decode($hqEmails, true);
                                                $hqEmails = is_array($hqEmails) && count($hqEmails) ? $hqEmails : [''];
                                            @endphp
                                            <div class="mb-3">
                                                <label class="form-label">Headquarters Emails</label>
                                                <div id="footer-hq-emails">
                                                    @foreach ($hqEmails as $email)
                                                        <div class="d-flex gap-2 mb-2">
                                                            <input type="text" class="form-control"
                                                                name="key[footer_hq_email][]" value="{{ $email }}"
                                                                placeholder="Enter ...">
                                                            <button type="button" class="btn btn-danger remove-field">Remove</button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button type="button" class="btn btn-secondary btn-sm add-more" data-target="#footer-hq-emails"
                                                    data-name="key[footer_hq_email][]">Add Email</button>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Mailing Address</label>
                                                <textarea id="key[footer_mailing_address]" name="key[footer_mailing_address]" class="form-control h-120"
                                                    placeholder="Enter ...">{{ $settingsArr['footer_mailing_address'] ?? '' }}</textarea>
                                            </div>
                                            @php
                                                $hqPhones = $settingsArr['footer_mailing_phone'] ?? '[]';
                                                $hqPhones = is_array($hqPhones) ? $hqPhones : json_decode($hqPhones, true);
                                                $hqPhones = is_array($hqPhones) && count($hqPhones) ? $hqPhones : [''];
                                            @endphp
                                            <div class="mb-3">
                                                <label class="form-label">Mailing Phones</label>
                                                <div id="footer-hq-phones">
                                                    @foreach ($hqPhones as $phone)
                                                        <div class="d-flex gap-2 mb-2">
                                                            <input type="text" class="form-control"
                                                                name="key[footer_mailing_phone][]" value="{{ $phone }}"
                                                                placeholder="Enter ...">
                                                            <button type="button" class="btn btn-danger remove-field">Remove</button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button type="button" class="btn btn-secondary btn-sm add-more" data-target="#footer-hq-phones"
                                                    data-name="key[footer_mailing_phone][]">Add Phone</button>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Mailing Phone Hours (optional)</label>
                                                <input type="text" class="form-control" id="key[footer_mailing_phone_hours]"
                                                    name="key[footer_mailing_phone_hours]" value="{{ $settingsArr['footer_mailing_phone_hours'] ?? '' }}"
                                                    placeholder="Enter ...">
                                            </div>
                                            @php
                                                $hqEmails = $settingsArr['footer_mailing_email'] ?? '[]';
                                                $hqEmails = is_array($hqEmails) ? $hqEmails : json_decode($hqEmails, true);
                                                $hqEmails = is_array($hqEmails) && count($hqEmails) ? $hqEmails : [''];
                                            @endphp
                                            <div class="mb-3">
                                                <label class="form-label">Mailing Emails</label>
                                                <div id="footer-hq-emails">
                                                    @foreach ($hqEmails as $email)
                                                        <div class="d-flex gap-2 mb-2">
                                                            <input type="text" class="form-control"
                                                                name="key[footer_mailing_email][]" value="{{ $email }}"
                                                                placeholder="Enter ...">
                                                            <button type="button" class="btn btn-danger remove-field">Remove</button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <button type="button" class="btn btn-secondary btn-sm add-more" data-target="#footer-hq-emails"
                                                    data-name="key[footer_mailing_email][]">Add Email</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3 align-center">
                                        <div class="col-md-2">
                                            <label class="form-label">Admin Email: </label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" id="key[admin_email]"
                                                name="key[admin_email]" value="{{ $settingsArr['admin_email'] ?? '' }}"
                                                placeholder="Enter Admin Email Id" required>
                                        </div>
                                    </div>

                                </div>
                                <div class="card-footer d-flex ">
                                    <button type="submit" class="btn btn btn-primary mr-2 save-btn" name="save_btn"
                                        id="save_btn" value="Submit">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('custom-scripts')
    <script src="{{ asset('plugins/ckeditor/ckeditor.js') }}"></script>
    <script>
        window.addEventListener('load', function() {
            $(document).on('click', '.add-more', function() {
                const target = $(this).data('target');
                const name = $(this).data('name');
                const field = `
                    <div class="d-flex gap-2 mb-2">
                        <input type="text" class="form-control" name="${name}" value="" placeholder="Enter ...">
                        <button type="button" class="btn btn-danger remove-field">Remove</button>
                    </div>
                `;
                $(target).append(field);
            });

            $(document).on('click', '.remove-field', function() {
                const container = $(this).closest('div');
                const wrapper = container.parent();
                if (wrapper.children().length > 1) {
                    container.remove();
                } else {
                    container.find('input').val('');
                }
            });

            $('.save-btn').on('click', function(e) {
                e.preventDefault();
                let validator = validateForm($('form[name="settingsForm"]'), {}, {})
                if (validator.form()) {
                    $('form[name="settingsForm"]').submit();
                }
            })
        });
    </script>
@endpush
