<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\FormDataCommentController;
use App\Http\Controllers\FormDataController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ManageProfileController;
use App\Http\Controllers\ManageSettingsController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\UploadController;

use App\Http\Controllers\Superadmin\PackageController;
use App\Http\Controllers\Superadmin\SuperadminSettingsController;
use App\Http\Controllers\Superadmin\ManageUsersController;
use App\Http\Controllers\Superadmin\PackageSubscriptionsController;
use App\Http\Controllers\Superadmin\SubscriptionPaymentController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['IsInstalled'])->group(function () {
    Auth::routes(['register' => env('ENABLE_REGISTRATION', false)]);

    Route::post('registration', [RegistrationController::class, 'store']);
});

Route::middleware(['IsInstalled', 'auth', 'bootstrap', 'setDefaultConfig'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/home-template', [HomeController::class, 'getTemplate']);
    Route::get('/home-assigned-forms', [HomeController::class, 'getAssignedForms']);
    Route::get('/test-smtp', [HomeController::class, 'testSMTP']);

    Route::post('toggle-global-template', [FormController::class, 'toggleGlobalTemplate'])
        ->name('toggle.global.template');
    Route::get('generate-form-slug', [FormController::class, 'generateFormSlug']);
    Route::get('form-copy/{id}', [FormController::class, 'copyForm']);
    Route::get('form-generate-widget/{id}', [FormController::class, 'generateWidget']);
    Route::resource('forms', FormController::class)
        ->except(['show']);
    Route::get('/form/{id}/download-code', [FormController::class, 'downloadCode']);
    Route::get('/get-acelle-list-ids', [FormController::class, 'getAcelleMailListIds']);
    Route::get('/get-acelle-list-info', [FormController::class, 'getAcelleMailListInfo']);
    Route::get('/form/{id}/get-collaborate', [FormController::class, 'getCollab']);
    Route::post('/form/post-collaborate', [FormController::class, 'postCollab']);
    Route::get('/edit/{slug}/data/{id}', [FormDataController::class, 'getEditformData']);
    Route::post('/update/{slug}/data/{id}', [FormDataController::class, 'postEditformData']);
    Route::get('/form-data/{id}', [FormDataController::class, 'show']);
    Route::get('/form-data-view/{id}', [FormDataController::class, 'viewData']);
    Route::get('/download/{id}/pdf', [FormDataController::class, 'downloadPdf']);
    Route::delete('/form-data-destroy/{id}', [FormDataController::class, 'destroy']);
    Route::get('/form-data-report/{id}', [FormDataController::class, 'getReport']);
    Route::get('manage-profile', [ManageProfileController::class, 'getProfile']);
    Route::put('manage-profile-update/{id}', [ManageProfileController::class, 'postProfile']);

    Route::get('user-settings', [ManageSettingsController::class, 'getSettings']);
    Route::post('post-user-settings', [ManageSettingsController::class, 'postSettings']);

    Route::get('subscriptions', [SubscriptionsController::class, 'index']);
    Route::get('subscriptions/{id}', [SubscriptionsController::class, 'show']);
    Route::get('all-subscriptions', [SubscriptionsController::class, 'getAllSubscriptions']);
    Route::resource('form-data-comment', FormDataCommentController::class)->only(['store', 'destroy']);
});

//Superadmin
Route::prefix('superadmin')
    ->middleware(['IsInstalled', 'auth', 'bootstrap', 'setDefaultConfig'])
    ->group(function () {
        Route::resource('packages', PackageController::class);
        Route::resource('superadmin-settings', SuperadminSettingsController::class);
        Route::get('users/{id}/toggle-status', [ManageUsersController::class, 'toggleUserActiveStatus']);
        Route::post('users/check-email-exist', [ManageUsersController::class, 'checkIfEmailExist']);
        Route::get('users/{id}/upgrade', [ManageUsersController::class, 'upgrade']);
        Route::resource('users', ManageUsersController::class);
        Route::get('package-subscription', [PackageSubscriptionsController::class, 'index']);
        Route::get('package-subscription/{id}/edit', [PackageSubscriptionsController::class, 'edit']);
        Route::put('package-subscription/{id}', [PackageSubscriptionsController::class, 'update']);

        Route::get('subscription/{package_id}/register-pay', [SubscriptionPaymentController::class, 'subscriptionPay']);
        Route::get('subscription/{package_id}/pay', [SubscriptionPaymentController::class, 'pay']);
        Route::any('subscription/{package_id}/confirm', [SubscriptionPaymentController::class, 'confirmPayment']);
        Route::post('subscription/{package_id}/offline-payment', [SubscriptionPaymentController::class, 'pay_offline']);
        Route::get('subscription/{package_id}/paypal-payment', [SubscriptionPaymentController::class, 'pay_paypal']);
        Route::get(
            'subscription/{package_id}/paypal-express-checkout',
            [SubscriptionPaymentController::class, 'paypalExpressCheckout']
        );

        Route::get(
            'confirm-subscription/{package_id}/admin/{user_id}',
            [SubscriptionPaymentController::class, 'confirmAdminSubscription']
        );
        Route::any(
            'subscription/{package_id}/admin/{user_id}',
            [SubscriptionPaymentController::class, 'adminSubscription']
        );
    });

Route::middleware(['IsInstalled', 'bootstrap'])->group(function () {
    Route::post('/validate-input-value', [FormController::class, 'validateInputValue']);

    Route::resource('forms', FormController::class)
        ->only(['show']);
    Route::get('password-protected/{id}/form', [FormController::class, 'validatePasswordForProtectedForm'])
        ->name('validate.protected.form');

    Route::post('post-validate-protected/{id}/form', [FormController::class, 'postValidatePasswordForProtectedForm'])
        ->name('post.validate.protected.form');

    Route::get('form-examples', [FormController::class, 'getFormExamples'])->name('form-examples');
    Route::post('/form-data/{id}', [FormDataController::class, 'store']);
    Route::post('/file-upload', [UploadController::class, 'upload']);
    Route::post('/file-delete', [UploadController::class, 'deleteFile']);
    Route::get('/existing-file-display', [UploadController::class, 'getExistingFiles']);
});

// Localization
Route::get('/js/lang.js', function () {
    $strings = Cache::remember('lang.js', 0, function () {
        $lang = config('app.locale');

        $files = glob(base_path('lang/'.$lang.'/*.php'));
        $strings = [];

        foreach ($files as $file) {
            $name = basename($file, '.php');
            $strings[$name] = require $file;
        }

        return $strings;
    });

    header('Content-Type: text/javascript');
    echo 'window.i18n = '.json_encode($strings).';';
    exit();
})->name('assets.lang');

include_once 'ir.php';
