<?php

use App\Http\Controllers\Api\V1\AuthController;
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
    $router->prefix("auth")->controller(AuthController::class)->group(function ($router)  {
        $router->post("/signup", 'signup'); 
        $router->post("/resend_verification_otp", 'resend_verification_otp'); 
        
        $router->post("/login", 'login'); 
        $router->post("/forgot_password", 'forgot_password'); 
        $router->post("/reset_password", 'reset_password'); 
        $router->post("/verify_new_account", 'verify_new_account'); 
    });
    $router->middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

});
