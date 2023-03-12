<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\HealthRecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::prefix("v1")->group(function ($router) {
    $router->prefix("auth")->controller(AuthController::class)->group(function ($router) {
        $router->post("/signup", 'signup');
        $router->post("/resend_verification_otp", 'resend_verification_otp');

        $router->post("/login", 'login');
        $router->post("/forgot_password", 'forgot_password');
        $router->post("/reset_password", 'reset_password');
        $router->post("/verify_new_account", 'verify_new_account');

    });
    $router->post("/check_otp", [AuthController::class, "check_otp"]);
    $router->middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
    $router->prefix("user")->middleware('auth:sanctum')->group(function ($router) {
        $router->post("/update_health_records", [HealthRecordController::class, 'update_health_records']);
        $router->get("/leaderboard", [HealthRecordController::class, 'leaderboard']);
        
    });

});
Route::fallback(function () {
    return errorResponse([], "Invalid Route name.", 404);
});
