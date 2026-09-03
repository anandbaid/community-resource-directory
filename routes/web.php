<?php

use App\Http\Controllers\Auth\UserLoginController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\LibraryController;
use App\Http\Controllers\Frontend\OrganizationController;
use App\Http\Controllers\Frontend\StaticPageController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:cache');
    $exitCode = Artisan::call('queue:restart');
    return 'DONE'; //Return anything
});

Route::get('/', [HomeController::class, 'home']);
Route::get('/index', [HomeController::class, 'home']);
Route::get('/home', [HomeController::class, 'home'])->name('home');

// auth routes
Route::get('/register', [UserLoginController::class, 'register']);
Route::post('/register-submit', [UserLoginController::class, 'registerUsers'])->name('register-submit')->middleware('recaptcha');
Route::get('/login', [UserLoginController::class, 'loginView']);
Route::post('/login', [UserLoginController::class, 'login'])->name('login')->middleware('recaptcha');
Route::get('/logout', [UserLoginController::class, 'logout']);

// password reset routes
Route::get('/password-reset', [UserLoginController::class, 'showResetForm'])->name('password.request');
Route::post('/password-reset', [UserLoginController::class, 'sendResetLink'])->name('password.email')->middleware('recaptcha');
Route::get('/password-reset/{token}', [UserLoginController::class, 'showNewPasswordForm'])->name('password.reset');
Route::post('/password-reset/update', [UserLoginController::class, 'updatePassword'])->name('password.update')->middleware('recaptcha');

// static pages
Route::get('/about-us', [StaticPageController::class, 'aboutUs'])->name('about-us');
Route::get('/contact-us', [StaticPageController::class, 'contactUs'])->name('contact-us');
Route::post('/contact-us', [StaticPageController::class, 'contactSubmit'])->middleware('recaptcha');
Route::get('/support-us', [StaticPageController::class, 'supportUs'])->name('support-us');
Route::get('/our-partners', [StaticPageController::class, 'ourPartners'])->name('our-partners');
Route::get('/privacy-policy', [StaticPageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-conditions', [StaticPageController::class, 'termsConditions'])->name('terms-conditions');
Route::get('/career-success-hub', [StaticPageController::class, 'career'])->name('career');
Route::get('/career-success-hub/topic/{topic}', [StaticPageController::class, 'careerTopic'])->name('career-topic');

Route::middleware('user')->group(function () {
    Route::get('/suggest-new-resources', [OrganizationController::class, 'suggestNewOrganization']);
    Route::get('/suggest-existing-resources', [OrganizationController::class, 'suggestExistingOrganization']);
    Route::get('/get-suggested-fields', [OrganizationController::class, 'getFields'])->name('get-suggested-fields');
    Route::post('/suggest-resources', [OrganizationController::class, 'suggestOrganizationSubmit'])->name('submit-suggestions')->middleware('recaptcha');
    Route::post('/check-publication-titles', [OrganizationController::class, 'checkPublicationTitles'])->name('check-publication-titles');

    Route::get('/review-rating/{id}', [OrganizationController::class, 'reviewRating']);
    Route::post('/submit-review', [OrganizationController::class, 'reviewSubmit'])->middleware('recaptcha');

    Route::get('/saved-resource/{id}', [OrganizationController::class, 'savedResource']);
    Route::get('/saved-resources-view', [OrganizationController::class, 'savedResourceView']);
    Route::get('/download-saved-resources', [OrganizationController::class, 'downloadSavedResources'])->name('download-saved-resources');

    Route::post('/save-search', [OrganizationController::class, 'saveSearch']);
    Route::get('/saved-search-view', [OrganizationController::class, 'savedSearchView']);
    Route::get('/download-search/{id}', [OrganizationController::class, 'downloadSearch']);
    Route::delete('/delete-search/{id}', [OrganizationController::class, 'deleteSearch']);
    Route::post('/report-spam', [OrganizationController::class, 'report_spam'])->name('report-spam')->middleware('recaptcha');
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/profile', [HomeController::class, 'profile'])->name('user.profile');
    Route::post('/profile', [HomeController::class, 'updateProfile'])->name('user.profile.update');
});

// Organization routes
Route::get('/search-resources', [OrganizationController::class, 'resources'])->name('search-resources');
Route::get('/organization-details/{id}', [OrganizationController::class, 'organizationDetails']);
Route::get('/get-more-publication/{id}', [OrganizationController::class, 'loadMorePublication']);

// Library routes
Route::get('/library', [LibraryController::class, 'index'])->name('library');
Route::get('/library/{id}', [LibraryController::class, 'show'])->name('library.show');
Route::get('/download-resource/{id}', [LibraryController::class, 'downloadResource']);

// Dynamic static pages (must be last)
Route::get('/{slug}', [StaticPageController::class, 'dynamicPage'])
    ->where('slug', '^(?!admin(?:/|$))[a-z0-9]+(?:-[a-z0-9]+)*$');
