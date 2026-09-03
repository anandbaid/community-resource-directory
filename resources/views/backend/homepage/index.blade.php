@extends('backend.layouts.app')
@section('title', ' | Home Page Sections')
@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Home Page Sections</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Home Page Sections
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between">
                            <h5 class="card-title mb-0">Home Page Sections</h5>
                        </div>
                        <div class="card-body">
                            <form name="homeSectionsForm" id="homeSectionsForm" class="px-3"
                                action="{{ route('admin.home-sections.save') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <h6 class="mb-3"><b>Resource Directory</b></h6>
                                    <div class="form-group row mb-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Paragraph 1</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control h-300 ck-editor-content" name="key[home_resource_block_1]">{!! $settingsArr['home_resource_block_1'] ??
                                                '<h3 data-aos="fade-up">COMMUNITY RESOURCE DIRECTORY<br><span class="lt sz-30">a service of Community Resource Directory!</span></h3>
                                                                                        <p class="bigPara mb-5" data-aos="fade-up">The newest addition to our extensive collection of community resources, U.S. Community represents an unprecedented realignment of our organizational priorities. U.S. Community is America&#39;s first and only research-based resource directory for individuals, families and communities impacted by the criminal legal system.</p>' !!}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Paragraph 2</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control h-300 ck-editor-content" name="key[home_resource_block_2]">{!! $settingsArr['home_resource_block_2'] ??
                                                '<h3 class="mb-2" data-aos="fade-up">DESIGNED TO SERVE. ENGINEERED TO DELIVER.</h3>
                                                                                        <p class="bigPara" data-aos="fade-up">The U.S. Community Resource Directory contains verified resources compiled to enhance community success. Explore thousands of service providers and discover the tools to achieve lifelong success.</p>' !!}</textarea>
                                        </div>
                                    </div>

                                    <h6 class="mb-3"><b>What We Do</b></h6>
                                    <div class="form-group row mb-4">
                                        <div class="col-md-2">
                                            <label class="form-label">Paragraph</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control h-300 ck-editor-content" name="key[home_what_we_do_block]">{!! $settingsArr['home_what_we_do_block'] ??
                                                '<h2 data-aos="fade-up"><span class="lt">What</span> We Do and Why?</h2>
                                                                                        <p data-aos="fade-up">Our data, gathered from thousands of returning citizens, tells an indisputable story: Successful community requires a strategic approach, valid and reliable resources, and communities willing to explore new opportunities for redemption. Having discovered this, we focus our efforts on four key priorities:</p>' !!}</textarea>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 1 Title</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input class="form-control" name="key[home_what_we_do_item_1_title]"
                                                        value="{{ $settingsArr['home_what_we_do_item_1_title'] ?? 'Advocating' }}">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 1 Description</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <textarea class="form-control" name="key[home_what_we_do_item_1_desc]" rows="3">{{ $settingsArr['home_what_we_do_item_1_desc'] ?? 'Advocating for social supports, criminal legal system reforms and policy changes.' }}</textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 1 Image</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control"
                                                        name="home_what_we_do_item_1_image" accept="image/*">
                                                    @php
                                                        $item1Image =
                                                            $settingsArr['home_what_we_do_item_1_image'] ?? '';
                                                    @endphp
                                                    <img src="{{ $item1Image ? asset($item1Image) : asset('assets/img/advocating.jpg') }}"
                                                        alt="Item 1" class="mt-2" style="max-width: 120px;">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 1 Icon</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control"
                                                        name="home_what_we_do_item_1_icon" accept="image/*">
                                                    @php
                                                        $item1Icon = $settingsArr['home_what_we_do_item_1_icon'] ?? '';
                                                    @endphp
                                                    <img src="{{ $item1Icon ? asset($item1Icon) : asset('assets/img/advocating.png') }}"
                                                        alt="Item 1 Icon" class="mt-2"
                                                        style="max-width: 60px; background-color: #d1d1d1; padding: 6px; border-radius: 6px;">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 1 link</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input class="form-control" name="key[home_what_we_do_item_1_link]"
                                                        value="{{ $settingsArr['home_what_we_do_item_1_link'] ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 2 Title</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input class="form-control" name="key[home_what_we_do_item_2_title]"
                                                        value="{{ $settingsArr['home_what_we_do_item_2_title'] ?? 'Developing' }}">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 2 Description</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <textarea class="form-control" name="key[home_what_we_do_item_2_desc]" rows="3">{{ $settingsArr['home_what_we_do_item_2_desc'] ?? 'Developing evidence-based programming options for corrections and communities.' }}</textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 2 Image</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control"
                                                        name="home_what_we_do_item_2_image" accept="image/*">
                                                    @php
                                                        $item2Image =
                                                            $settingsArr['home_what_we_do_item_2_image'] ?? '';
                                                    @endphp
                                                    <img src="{{ $item2Image ? asset($item2Image) : asset('assets/img/devoloping.jpg') }}"
                                                        alt="Item 2" class="mt-2" style="max-width: 120px;">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 2 Icon</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control"
                                                        name="home_what_we_do_item_2_icon" accept="image/*">
                                                    @php
                                                        $item2Icon = $settingsArr['home_what_we_do_item_2_icon'] ?? '';
                                                    @endphp
                                                    <img src="{{ $item2Icon ? asset($item2Icon) : asset('assets/img/developing.png') }}"
                                                        alt="Item 2 Icon" class="mt-2"
                                                        style="max-width: 60px; background-color: #d1d1d1; padding: 6px; border-radius: 6px;">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 2 link</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input class="form-control" name="key[home_what_we_do_item_2_link]"
                                                        value="{{ $settingsArr['home_what_we_do_item_2_link'] ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 3 Title</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input class="form-control" name="key[home_what_we_do_item_3_title]"
                                                        value="{{ $settingsArr['home_what_we_do_item_3_title'] ?? 'Educating' }}">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 3 Description</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <textarea class="form-control" name="key[home_what_we_do_item_3_desc]" rows="3">{{ $settingsArr['home_what_we_do_item_3_desc'] ?? 'Educating individuals and communities on the availability of effective community supports.' }}</textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 3 Image</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control"
                                                        name="home_what_we_do_item_3_image" accept="image/*">
                                                    @php
                                                        $item3Image =
                                                            $settingsArr['home_what_we_do_item_3_image'] ?? '';
                                                    @endphp
                                                    <img src="{{ $item3Image ? asset($item3Image) : asset('assets/img/service3.png') }}"
                                                        alt="Item 3" class="mt-2" style="max-width: 120px;">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 3 Icon</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control"
                                                        name="home_what_we_do_item_3_icon" accept="image/*">
                                                    @php
                                                        $item3Icon = $settingsArr['home_what_we_do_item_3_icon'] ?? '';
                                                    @endphp
                                                    <img src="{{ $item3Icon ? asset($item3Icon) : asset('assets/img/educating.png') }}"
                                                        alt="Item 3 Icon" class="mt-2"
                                                        style="max-width: 60px; background-color: #d1d1d1; padding: 6px; border-radius: 6px;">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 3 link</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input class="form-control" name="key[home_what_we_do_item_3_link]"
                                                        value="{{ $settingsArr['home_what_we_do_item_3_link'] ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 4 Title</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input class="form-control" name="key[home_what_we_do_item_4_title]"
                                                        value="{{ $settingsArr['home_what_we_do_item_4_title'] ?? 'Validating' }}">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 4 Description</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <textarea class="form-control" name="key[home_what_we_do_item_4_desc]" rows="3">{{ $settingsArr['home_what_we_do_item_4_desc'] ?? 'Validating resources to ensure support efficacy to advance community accessibility.' }}</textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 4 Image</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control"
                                                        name="home_what_we_do_item_4_image" accept="image/*">
                                                    @php
                                                        $item4Image =
                                                            $settingsArr['home_what_we_do_item_4_image'] ?? '';
                                                    @endphp
                                                    <img src="{{ $item4Image ? asset($item4Image) : asset('assets/img/validating.jpg') }}"
                                                        alt="Item 4" class="mt-2" style="max-width: 120px;">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 4 Icon</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control"
                                                        name="home_what_we_do_item_4_icon" accept="image/*">
                                                    @php
                                                        $item4Icon = $settingsArr['home_what_we_do_item_4_icon'] ?? '';
                                                    @endphp
                                                    <img src="{{ $item4Icon ? asset($item4Icon) : asset('assets/img/validating.png') }}"
                                                        alt="Item 4 Icon" class="mt-2"
                                                        style="max-width: 60px; background-color: #d1d1d1; padding: 6px; border-radius: 6px;">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Item 4 link</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input class="form-control" name="key[home_what_we_do_item_4_link]"
                                                        value="{{ $settingsArr['home_what_we_do_item_4_link'] ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="mb-3"><b>Our Shop</b></h6>
                                    <div class="form-group row mb-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Paragraph</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control h-300 ck-editor-content" name="key[home_shop_block]">{!! $settingsArr['home_shop_block'] ??
                                                '<h2 data-aos="fade-right"><span class="lt">Our</span> Shop</h2>
                                                                                        <p data-aos="fade-right">Browse our shop today and discover evidence-based materials designed to educate and inform. Cost-effective solutions designed for instructor-led and self-study delivery.</p>' !!}</textarea>
                                        </div>
                                    </div>

                                    <h6 class="mb-3"><b>About Section</b></h6>
                                    <div class="form-group row mb-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Paragraphs</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control h-300 ck-editor-content" name="key[home_about_block]">{!! $settingsArr['home_about_block'] ??
                                                '<h2><span class="lt">About</span> Community Resource Directory</h2>
                                                                                        <p>Community Resource Directory strives to aide those impacted by the criminal legal system through the development of evidence-based recidivism reduction strategies. It is through these strategies that Community Resource Directory works to make sure all justice impacted Americans have the opportunity to become law abiding, contributing members of society - regardless of their background.</p>
                                                                                        <p>Since 2017, Community Resource Directory has directly and indirectly helped thousands of individuals, families and communities across America.</p>
                                                                                        <p>Community Resource Directory has been working tirelessly with governments and communities across the country to break down barriers and identify real solutions.</p>' !!}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Image</label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="file" class="form-control" name="home_about_image"
                                                accept="image/*">
                                            @php
                                                $aboutImage = $settingsArr['home_about_image'] ?? '';
                                            @endphp
                                            <img src="{{ $aboutImage ? asset($aboutImage) : asset('assets/img/about_us.jpg') }}"
                                                alt="About Image" class="mt-2" style="max-width: 200px;">
                                        </div>
                                    </div>

                                    <h6 class="mb-3"><b>Career Success Hub</b></h6>
                                    <div class="form-group row mb-4">
                                        <div class="col-md-2">
                                            <label class="form-label">Paragraph</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control h-300 ck-editor-content" name="key[home_career_success_hub_block]">{!! $settingsArr['home_career_success_hub_block'] ??
                                                '<p>By donating your time, resources or expertise to Community Resource Directory, you&#39;re taking an active role in our overall success and we thank you for your support!</p>' !!}</textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Icon 1 Title</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input class="form-control" name="key[home_career_success_hub_icon_1_title]"
                                                        value="{{ $settingsArr['home_career_success_hub_icon_1_title'] ?? 'Job Search Tips' }}">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Icon 1</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control"
                                                        name="home_career_success_hub_icon_1_image" accept="image/*">
                                                    @php
                                                        $careericon1 = $settingsArr['home_career_success_hub_icon_1_image'] ?? '';
                                                    @endphp
                                                    <img src="{{ $careericon1 ? asset($careericon1) : asset('assets/img/Frame.png') }}"
                                                        alt="Item 1 Icon" class="mt-2"
                                                        style="max-width: 60px; background-color: #d1d1d1; padding: 6px; border-radius: 6px;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Icon 2 Title</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input class="form-control" name="key[home_career_success_hub_icon_2_title]"
                                                        value="{{ $settingsArr['home_career_success_hub_icon_2_title'] ?? 'Locate Job Openings' }}">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Icon 2 </label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control"
                                                        name="home_career_success_hub_icon_2_image" accept="image/*">
                                                    @php
                                                        $careericon2 = $settingsArr['home_career_success_hub_icon_2_image'] ?? '';
                                                    @endphp
                                                    <img src="{{ $careericon2 ? asset($careericon2) : asset('assets/img/Frame_1.png') }}"
                                                        alt="Item 2 Icon" class="mt-2"
                                                        style="max-width: 60px; background-color: #d1d1d1; padding: 6px; border-radius: 6px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Icon 3 Title</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input class="form-control" name="key[home_career_success_hub_icon_3_title]"
                                                        value="{{ $settingsArr['home_career_success_hub_icon_3_title'] ?? 'Market Yourself' }}">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Icon 3</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control"
                                                        name="home_career_success_hub_icon_3_image" accept="image/*">
                                                    @php
                                                        $careericon3 = $settingsArr['home_career_success_hub_icon_3_image'] ?? '';
                                                    @endphp
                                                    <img src="{{ $careericon3 ? asset($careericon3) : asset('assets/img/Frame_2.png') }}"
                                                        alt="Item 3 Icon" class="mt-2"
                                                        style="max-width: 60px; background-color: #d1d1d1; padding: 6px; border-radius: 6px;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Icon 4 Title</label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input class="form-control" name="key[home_career_success_hub_icon_4_title]"
                                                        value="{{ $settingsArr['home_career_success_hub_icon_4_title'] ?? 'Interview Prep' }}">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Icon 4 </label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="file" class="form-control"
                                                        name="home_career_success_hub_icon_4_image" accept="image/*">
                                                    @php
                                                        $careericon4 = $settingsArr['home_career_success_hub_icon_4_image'] ?? '';
                                                    @endphp
                                                    <img src="{{ $careericon4 ? asset($careericon4) : asset('assets/img/Frame_3.png') }}"
                                                        alt="Item 4 Icon" class="mt-2"
                                                        style="max-width: 60px; background-color: #d1d1d1; padding: 6px; border-radius: 6px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Image</label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="file" class="form-control" name="home_career_success_hub_image"
                                                accept="image/*">
                                            @php
                                                $aboutImage = $settingsArr['home_career_success_hub_image'] ?? '';
                                            @endphp
                                            <img src="{{ $aboutImage ? asset($aboutImage) : asset('assets/img/carrer-success.jpg') }}"
                                                alt="Career Hub Image" class="mt-2" style="max-width: 200px;">
                                        </div>
                                    </div>

                                    <h6 class="mb-3"><b>Support Us</b></h6>
                                    <div class="form-group row mb-4">
                                        <div class="col-md-2">
                                            <label class="form-label">Paragraph</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control h-300 ck-editor-content" name="key[home_support_block]">{!! $settingsArr['home_support_block'] ??
                                                '<h2>Support <span class="lt">Us</span></h2>
                                                                                        <p>By donating your time, resources or expertise to Community Resource Directory, you&#39;re taking an active role in our overall success and we thank you for your support!</p>' !!}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex">
                                    <button type="submit" class="btn btn-primary save-btn">Submit</button>
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
            $('.save-btn').on('click', function(e) {
                e.preventDefault();
                let validator = validateForm($('form[name="homeSectionsForm"]'), {}, {})
                if (validator.form()) {
                    $('form[name="homeSectionsForm"]').submit();
                }
            })
        });
    </script>
@endpush
