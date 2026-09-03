@extends('backend.layouts.app')
@section('title', ' | Email Templates')
@section('content')

    <div class="app-content-header"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Email Templates</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Email Templates
                        </li>
                    </ol>
                </div>
            </div> <!--end::Row-->
        </div> <!--end::Container-->
    </div> <!--end::App Content Header-->


    <div class="app-content"> <!--begin::Container-->
        <div class="container-fluid"> <!--begin::Row-->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Email Templates</h5>
                            <br class="float-none">
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-striped data-table">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ __('Name') }}</th>
                                        <th scope="col">{{ __('Title') }}</th>
                                        <th scope="col">{{ __('Status') }}</th>
                                        <th scope="col">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!$emailTemplates->isEmpty())
                                        @foreach ($emailTemplates as $emailTemplate)
                                            <tr>
                                                <td>{{ $emailTemplate->name ?? '' }}</td>
                                                <td>{{ $emailTemplate->title ?? '' }}</td>
                                                <td>{{ ucfirst($emailTemplate->status) ?? '' }}</td>
                                                <td>
                                                    <a href="{{ route('admin.emailtemplate.edit', $emailTemplate->id) }}"
                                                        class="btn btn-primary"><i class="fa-solid fa-pen"></i></a>
                                                    @if ($emailTemplate->status == 'active')
                                                        <button type="button" onclick="statusUpdate($(this))"
                                                            data-status="inactive" data-method="PATCH"
                                                            data-message="To Inactive This"
                                                            data-url="{{ route('admin.emailtemplate.status', $emailTemplate->id) }}"
                                                            class="btn btn-secondary" title="Inactive">
                                                            <i class="fas fa-lightbulb icon-spacer"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" onclick="statusUpdate($(this))"
                                                            data-status="active" data-method="PATCH"
                                                            data-message="To Active This"
                                                            data-url="{{ route('admin.emailtemplate.status', $emailTemplate->id) }}"
                                                            class="btn btn-warning" title="Active">
                                                            <i class="fas fa-lightbulb icon-spacer"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center">No record found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
