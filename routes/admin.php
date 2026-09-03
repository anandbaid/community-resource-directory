<?php

use App\Http\Controllers\admin\AdminRouteController;
use App\Http\Controllers\admin\BannerController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\CkeditorUploadController;
use App\Http\Controllers\admin\EmailTemplateController;
use App\Http\Controllers\admin\OrganizationController;
use App\Http\Controllers\admin\PublicationController;
use App\Http\Controllers\admin\QueryController;
use App\Http\Controllers\admin\ReviewController;
use App\Http\Controllers\admin\SavedSearchController;
use App\Http\Controllers\admin\StaticPageController;
use App\Http\Controllers\admin\UserDetailsController;
use App\Http\Controllers\Auth\AdminLoginController;
use Illuminate\Support\Facades\Route;

// admin auth routes
Route::get('login', [AdminLoginController::class, 'loginView']);
Route::post('login', [AdminLoginController::class, 'login'])->name('login');
Route::get('logout', [AdminLoginController::class, 'logout'])->name('logout');

Route::middleware('admin')->group(function () {
    // admin basic routes
    Route::get('/', [AdminRouteController::class, 'index'])->name('home');
    Route::get('dashboard', [AdminRouteController::class, 'index'])->name('dashboard');

    //site settings
    Route::get('general-settings', [AdminRouteController::class, 'generalSettings'])->name('generalSettings');
    Route::post('save-settings', [AdminRouteController::class, 'saveSettings'])->name('saveSettings');
    Route::post('clear-cache', [AdminRouteController::class, 'clearCacheAndVersion'])->name('clear-cache');

    // admin profile routes
    // Both of these were named 'profile', which made route:cache refuse to run
    // ("has already been assigned name [admin.profile]"). Same URI either way,
    // so every existing route('admin.profile') call still resolves correctly.
    Route::get('profile', [AdminRouteController::class, 'viewProfile'])->name('profile');
    Route::post('profile', [AdminRouteController::class, 'updateProfile'])->name('profile.update');


    // admin category routes
    Route::resource('categories', CategoryController::class)->names('category');

    // admin email template routes
    Route::resource('email-template', EmailTemplateController::class)->names('emailtemplate');
    Route::patch('email-template/statusupdate/{id}', [EmailTemplateController::class, 'updateStatus'])->name('emailtemplate.status');

    // admin user routes
    Route::resource('users', UserDetailsController::class)->names('user');
    Route::patch('users/statusupdate/{id}', [UserDetailsController::class, 'updateStatus'])->name('user.status');

    // admin publication routes
    Route::resource('publications', PublicationController::class)->names('publication');
    Route::post('ckeditor/upload-image', [CkeditorUploadController::class, 'uploadImage'])->name('ckeditor.upload-image');
    Route::post('ckeditor/upload-file', [CkeditorUploadController::class, 'uploadFile'])->name('ckeditor.upload-file');
    // admin organization routes
    Route::delete('organizations/bulk-delete', [OrganizationController::class, 'bulkDestroy'])->name('organization.bulk-destroy');
    Route::resource('organizations', OrganizationController::class)->names('organization');
    Route::post('organizations/manual-validate', [OrganizationController::class, 'manualValidate'])->name('organization.manual-validate');
    Route::patch('organizations/statusupdate/{id}', [OrganizationController::class, 'updateStatus'])->name('organization.status');
    Route::get('suggested-organizations', [OrganizationController::class, 'suggestedOrganizations'])->name('suggested-organizations.index');
    Route::get('suggested-organizations/{id}/edit', [OrganizationController::class, 'suggestedOrganizationsEdit'])->name('suggested-organizations.edit');
    Route::post('suggested-organizations/{id}', [OrganizationController::class, 'suggestedOrganizationsUpdate'])->name('suggested-organizations.update');

    Route::get('spam-report/{id?}', [OrganizationController::class, 'spam_report'])
        ->name('spam-report');
    Route::get('export', [OrganizationController::class, 'ExportOrganizations'])->name('organization.export');
    Route::get('bulk-import', [OrganizationController::class, 'BulkImport'])->name('bulk-import');
    Route::post('bulk-import', [OrganizationController::class, 'BulkImportSubmit'])->name('bulk-import-submit');
    Route::get('saved-searches', [SavedSearchController::class, 'index'])->name('saved-searches.index');
    Route::get('saved-searches/{id}/download', [SavedSearchController::class, 'download'])->name('saved-searches.download');
    Route::delete('saved-searches/{id}', [SavedSearchController::class, 'destroy'])->name('saved-searches.destroy');

    Route::get('email-check', [AdminRouteController::class, 'emailCheck'])->name('emailCheck');
    Route::get('validate-website', [AdminRouteController::class, 'validateWebsite'])->name('validateWebsite');

    // review controllers
    Route::resource('reviews', ReviewController::class)->names('review');

    // Static Page Controllers
    Route::resource('static-pages', StaticPageController::class)->names('static-pages');
    Route::post('static-pages/assets', [StaticPageController::class, 'uploadAsset'])->name('static-pages.assets');
    Route::get('home-sections', [StaticPageController::class, 'homeSections'])->name('home-sections');
    Route::post('home-sections', [StaticPageController::class, 'saveHomeSections'])->name('home-sections.save');
    Route::get('resources', [StaticPageController::class, 'resources'])->name('resources');
    Route::post('resources', [StaticPageController::class, 'saveResources'])->name('resources.save');
    Route::get('library', [StaticPageController::class, 'library'])->name('library');
    Route::post('library', [StaticPageController::class, 'saveLibrary'])->name('library.save');

    // Query Controllers
    Route::resource('queries', QueryController::class)->names('queries');

    // banner Controllers
    Route::resource('banners', BannerController::class)->names('banner');
});
