<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Api\LoginController;

use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\ManagerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::post('login', 'ApiController@login');
Route::post('manager/login', [\App\Http\Controllers\Api\LoginController::class, 'mangerlogin']);
Route::post('driver/login', [\App\Http\Controllers\Api\LoginController::class, 'driverlogin']);
Route::get('/policy-content/{id}', [DriverController::class, 'showContent']);
Route::get('/update-driver-consent', [DriverController::class, 'updateConsentValid']);
Route::get('/driver/get/token', [LoginController::class, 'getTokenAPI']);
Route::post('/third-party/driver-details', [LoginController::class, 'fetchDriverDetails']);


Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('logout', [ApiController::class, 'logout']);
    Route::get('get-projects', [ApiController::class, 'getProjects']);
    Route::post('add-tracker', [ApiController::class, 'addTracker']);
    Route::post('stop-tracker', [ApiController::class, 'stopTracker']);
    Route::post('upload-photos', [ApiController::class, 'uploadImage']);

    // manager
    Route::get('count/data', [ManagerController::class, 'getCompanyData']);
    Route::get('driver/vehicle/opc/data', [ManagerController::class, 'getDriverVehicleOPCData']);
        Route::post('driver/data', [ManagerController::class, 'getDriverDetailsData']);
    Route::post('/vehicle/data', [ManagerController::class, 'getVehicleDetailsData']);
    Route::post('/operating/data', [ManagerController::class, 'getOperatingDetailsData']);
        Route::get('/get-manager-profile', [ManagerController::class, 'getmanagerProfile']);
        Route::post('/upload-driver-document', [ManagerController::class, 'uploadDriverAttachment']);
        Route::post('/upload-vehicle-document', [ManagerController::class, 'uploadVehicleAttachment']);
        Route::post('/manager/get/preview/walkaround', [ManagerController::class, 'getpreviewwalkaroundManager']);
        Route::post('/manager/get/preview/walkaround/details', [ManagerController::class, 'getpreviewwalkaroundDetailsManager']);
    Route::post('/manager/walkaround/defect/details', [ManagerController::class, 'getdefectwalkaroundDetailsManager']);
    Route::post('/manager/store/defect/rectifield', [ManagerController::class, 'storedefectwalkaroundRectifieldManager']);
        Route::post('/manager/get/vehiclelist', [ManagerController::class, 'getvehiclelistManager']);
        Route::post('/manager/defect/update', [ManagerController::class, 'updateDefect']);
        Route::get('/manager/notification', [ManagerController::class, 'getManagerNotification']);
        Route::post('/manager/notification/delete', [ManagerController::class, 'deleteNotification']);
        Route::post('/manager/training', [ManagerController::class, 'getManagerTrainingAssignments']);
        Route::post('/manager/planner', [ManagerController::class, 'getManagerPlanner']);
        Route::post('/manager/profile/upload', [ManagerController::class, 'uploadprofileimage']);
        Route::post('/manager/logout', [\App\Http\Controllers\Api\LoginController::class, 'managerlogout']);
        Route::post('/manager/walkaround/preference/update', [ManagerController::class, 'updateWalkaroundPreference']);

});

// Driver APP
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('get-driver-profile', [DriverController::class, 'getDriverProfile']);
    Route::get('get-policy-list', [DriverController::class, 'getAssignPolicyList']);
        Route::get('policy-list', [DriverController::class, 'getPolicyList']);
    Route::post('policy-details', [DriverController::class, 'getSpecificPolicyDetails']);
    Route::post('/signature-store', [DriverController::class, 'saveSignature']);
        Route::post('/driver/upload-document', [DriverController::class, 'upload']);
            Route::get('/driver/contactbook', [DriverController::class, 'getContactBook']);
                Route::post('/walkaround/profile', [DriverController::class, 'getProfileDetails']);
 Route::post('/walkaround/store/step1', [DriverController::class, 'storeworkaroundstep1']);
    Route::post('/walkaround/store/step2', [DriverController::class, 'storeworkaroundstep2']);
    Route::get('/walkaround/profile/Data', [DriverController::class, 'getProfileData']);
    Route::post('/walkaround/profile/question/bypage', [DriverController::class, 'getQuestionsByPage']);
Route::post('/update/walkaround/store/step1', [DriverController::class, 'updatestoreworkaroundstep1']);
    Route::post('/delete/oldwalkaround', [DriverController::class, 'deleteoldwalkaround']);
    Route::post('/get/preview/walkaround/list', [DriverController::class, 'getpreviewwalkaround']);
    Route::post('/get/preview/walkaround/details', [DriverController::class, 'getpreviewwalkaroundDetails']);
    Route::get('/get/vehiclelist', [DriverController::class, 'getvehiclelist']);
    Route::post('/get/vehicle/details', [DriverController::class, 'getDriverVehicleDetailsData']);
    Route::get('/driver/walkaround/defect/list', [DriverController::class, 'getdefectwalkaroundList']);
    Route::post('/driver/walkaround/defect/details', [DriverController::class, 'getdefectwalkaroundDetails']);
    Route::post('/defect-options', [DriverController::class, 'getDefectOptions']);
    Route::post('/driver/store/defect/rectifield', [DriverController::class, 'storedefectwalkaroundRectifield']);
    Route::get('/driver/count/data', [DriverController::class, 'getWalkaroundVehicleContactbookDefectsHandbookTrainingcount']);
    Route::post('/driver/latest/walkaround/defect/details', [DriverController::class, 'getLatestdefectwalkaroundDetails']);
    Route::get('/driver/notification', [DriverController::class, 'getDriverNotification']);
    Route::post('/driver/notification/delete', [DriverController::class, 'deleteDriverNotification']);
        Route::get('/driver/all/depot/list', [DriverController::class, 'getDriverCompanyDepots']);
    Route::post('/driver/update/depot', [DriverController::class, 'updateDriverDepot']);

    Route::post('/driver/training', [DriverController::class, 'getDriverTrainingAssignments']);
    Route::post('/driver/training/update', [DriverController::class, 'updateTrainingAssignment']);
Route::post('driver/consent/form', [DriverController::class, 'consentforms']);
 Route::post('/company-account-no', [DriverController::class, 'getCompanyAccountDetails']);
Route::post('/driver/{id}/update', [DriverController::class, 'updateSpecific']);
Route::post('/driver/logout', [\App\Http\Controllers\Api\LoginController::class, 'driverLogout']);
Route::post('/change/password', [\App\Http\Controllers\Api\LoginController::class, 'appchangePassword']);



});
Route::post('/retrieve-driver-data', [\App\Http\Controllers\DriverController::class, 'retrieveDriverData']);
