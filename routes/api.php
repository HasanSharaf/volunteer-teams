<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaignTypeController;
use App\Http\Controllers\Api\SpecializationController;
use App\Http\Controllers\Api\VolunteerTeamController;
use App\Http\Controllers\Api\BusinessInformationController;
use App\Http\Controllers\Api\VolunteerController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\PointController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\BenefactorController;
use App\Http\Controllers\Api\DonorPaymentController;
use App\Http\Controllers\Api\FinancialController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\OTPController;
use App\Http\Controllers\Api\GovernmentController;
use App\Http\Controllers\Api\GovernmentAuthController;

// Public routes
Route::post('/volunteer/register', [AuthController::class, 'volunteerRegister']);
Route::post('/volunteer/login', [AuthController::class, 'volunteerLogin']);

Route::post('/team/register', [AuthController::class, 'teamRegister']);
Route::post('/team/login', [AuthController::class, 'teamLogin']);
Route::post('/send-otp', [OTPController::class, 'sendOTP']);
Route::post('/verify-otp', [OTPController::class, 'verifyOTP']);
Route::post('/update/Password', [OTPController::class, 'updatePasswordForVolunteerEntities']);




// Protected routes
Route::middleware('auth:sanctum')->group(function () {


    
    //volunteer
    Route::get('/volunteer/profile', [AuthController::class, 'profileVolunteer']);
    Route::post('/volunteer/profile/update', [AuthController::class, 'updateProfilevolunteer']);

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Campaign Types routes
    Route::apiResource('campaign-types', CampaignTypeController::class);

    // Specializations routes
    Route::apiResource('specializations', SpecializationController::class);

    // Volunteer Teams routes
    Route::apiResource('volunteer-teams', VolunteerTeamController::class);
    Route::apiResource('business-information', BusinessInformationController::class);

    // Volunteers routes
    Route::apiResource('volunteers', VolunteerController::class);

    // Employees routes
    Route::apiResource('employees', EmployeeController::class);

    // Campaigns routes
    Route::apiResource('campaigns', CampaignController::class);
    Route::get('get/campaigns/By/Specialty', [CampaignController::class,'getcampaignsBySpecialty']);
    Route::post('campaigns/{campaign}/volunteers', [CampaignController::class, 'addVolunteer']);
    Route::delete('campaigns/{campaign}/volunteers/{volunteer}', [CampaignController::class, 'removeVolunteer']);

    // Points routes
    Route::apiResource('points', PointController::class);

    // Requests routes
    Route::apiResource('requests', RequestController::class);

    // Attendances routes
    Route::apiResource('attendances', AttendanceController::class);

    // Benefactors routes
    Route::apiResource('benefactors', BenefactorController::class);

    // Donor Payments routes
    Route::apiResource('donor-payments', DonorPaymentController::class);

    // Financials routes
    Route::apiResource('financials', FinancialController::class);

    // Chats routes
    Route::apiResource('chats', ChatController::class);

    // Contracts routes
    Route::apiResource('contracts', ContractController::class);


    Route::get('/get/all/volunteer/teams', [DonorPaymentController::class, 'getallteamaccepted']);
});

// Government Routes
Route::middleware(['auth:sanctum', 'government.only'])->group(function () {
    Route::get('/government/teams', [GovernmentController::class, 'getTeams']);
    Route::get('/government/teams/pending', [GovernmentController::class, 'getPendingTeams']);
    Route::post('/government/teams/{team}/approve', [GovernmentController::class, 'approveTeam']);
    Route::post('/government/teams/{team}/reject', [GovernmentController::class, 'rejectTeam']);
    Route::get('/government/teams/{team}', [GovernmentController::class, 'getTeamDetails']);
    Route::get('/government/teams/{team}/finance', [GovernmentController::class, 'getTeamFinance']);
    Route::get('/government/teams/{team}/campaigns', [GovernmentController::class, 'getTeamCampaigns']);
    Route::get('/government/teams/{team}/employees', [GovernmentController::class, 'getTeamEmployees']);
    Route::post('/government/campaigns/{campaign}/approve', [GovernmentController::class, 'approveCampaign']);
    Route::post('/government/campaigns/{campaign}/reject', [GovernmentController::class, 'rejectCampaign']);
});

// Government Authentication Routes
Route::prefix('government')->group(function () {
    Route::post('/register', [GovernmentAuthController::class, 'register']);
    Route::post('/login', [GovernmentAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [GovernmentAuthController::class, 'logout']);
    });
}); 