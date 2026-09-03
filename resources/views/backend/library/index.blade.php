@extends('backend.layouts.app')
@section('title', ' | Library Sections')
@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Library Sections</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Library Sections
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
                            <h5 class="card-title mb-0">Library Sections</h5>
                        </div>
                        <div class="card-body">
                            <form name="libraryForm" id="libraryForm" class="px-3"
                                action="{{ route('admin.library.save') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <h6 class="mb-3"><b>Library Content</b></h6>
                                    <div class="form-group row mb-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Paragraph 1</label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control h-300 ck-editor-content" name="library_block">{!! $libraryContent ??
                                                '' !!}</textarea>
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
    {{-- <script>
        window.addEventListener('load', function() {
            $('.save-btn').on('click', function(e) {
                e.preventDefault();
                let validator = validateForm($('form[name="homeSectionsForm"]'), {}, {})
                if (validator.form()) {
                    $('form[name="homeSectionsForm"]').submit();
                }
            })
        });
    </script> --}}
@endpush
