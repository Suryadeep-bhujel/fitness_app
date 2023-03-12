<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\SendPasswordResetOTPJob;
use App\Jobs\SendSignupOtpJob;
use App\Models\OtpList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "firstName" => "required|string|max:40",
            "middleName" => "nullable|string|max:40",
            "lastName" => "nullable|string|max:40",
            "email" => "required|string|email|unique:users,email",
            "mobile" => "required|string|min:10|max:18",
            "gender" => "required|string|in:Male,Female,Other",
            "dateOfBirth" => "required|date_format:d-m-Y|before:today",
            "password" => "required|string|min:8|max:32|same:confirm_password",
        ]);
        if ($validation->fails()) {
            return validateFormData($validation);
        }

        try {
            $user_data = [
                "firstName" => Str::ucfirst($request->firstName),
                "middleName" => Str::ucfirst($request->middleName),
                "lastName" => Str::ucfirst($request->lastName),
                "email" => Str::lower($request->email),
                "password" => Hash::make($request->password),
                "gender" => $request->gender,
                "birthDate" => $request->dateOfBirth,
                "mobile" => $request->mobile,
            ];
            DB::beginTransaction();
            $user = User::create($user_data);
            $otp_data = [
                "user_id" => $user->id,
                "otp" => create_otp(),
                "username" => $user->mobile,
            ];
            $otp = OtpList::create($otp_data);
            SendSignupOtpJob::dispatch($user, $otp->otp);
            DB::commit();
            $data = [];
            if (app()->environment() != 'production') {
                $data = [
                    "otp" => $otp->otp, // remove in production environment
                ];
            }
            return successResponse(201, "Account successfully created. An account Verification OTP has been sent to your mobile.", $data);
        } catch (\Throwable$th) {
            DB::rollback();
            report($th);
            return errorResponse($th, "Something went wrong while creating your account.");
        }

    }

    public function verify_new_account(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "mobile" => "required|string|min:10|max:18|exists:users,mobile",
            "otp" => "required|string|digits:" . OTP_SIZE,

        ]);
        if ($validation->fails()) {
            return validateFormData($validation);
        }
        try {
            $otpInfo = OtpList::whereOtp($request->mobile, $request->otp)->first();
            if (!$otpInfo) {
                return formErrorResponse(null, "Invalid OTP", 404);
            }
            if ($otpInfo->created_at < now()->subDays(5)) {
                return formErrorResponse(null, "OTP expired", 422);
            }
            DB::beginTransaction();
            User::where("mobile", $request->mobile)->update(['mobile_verified_at' => now()]);
            $otpInfo->delete();
            DB::commit();
            return successResponse(200, "Your account has been verified now. Please Log in");
        } catch (\Throwable$th) {
            DB::rollback();
            report($th);
            return errorResponse($th, "Something went wrong while verifying your account.");
        }

    }

    public function resend_verification_otp(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "mobile" => "required|string|min:10|max:18|exists:users,mobile",
        ]);
        if ($validation->fails()) {
            return validateFormData($validation);
        }
        try {
            $user = User::where("mobile", $request->mobile)->first();
            if (!$user) {
                return errorResponse([], "Account does not exist for this mobile number.", 404);
            }
            if ($user->mobile_verified_at && $user->mobile_verified_at < now()) {
                return errorResponse([], "Your mobile number was already verified.", 422);
            }
            $otpInfo = OtpList::oldOtp($user->id)->first();
            if ($otpInfo) {
                return errorResponse([], "Please wait for a minute to request new OTP.", 422);
            }
            $otp_data = [
                "user_id" => $user->id,
                "otp" => create_otp(),
                "username" => $user->mobile,
            ];
            $otp = OtpList::create($otp_data);
            SendSignupOtpJob::dispatch($user, $otp->otp);
            $data = [];
            if (app()->environment() != 'production') {
                $data = [
                    "otp" => $otp->otp, // remove in production environment
                ];
            }
            return successResponse(201, "An account Verification OTP has been sent to your mobile.", $data);
        } catch (\Throwable$th) {
            report($th);
            return errorResponse($th, "Something went wrong while sending account verification OTP.");
        }
    }
    public function forgot_password(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "mobile" => "required|string|min:10|max:18|exists:users,mobile",
        ]);
        if ($validation->fails()) {
            return validateFormData($validation);
        }

        try {
            $user = User::where("mobile", $request->mobile)->first();
            if (!$user) {
                return errorResponse([], "Account does not exist for this mobile number.", 404);
            }
            $otpInfo = OtpList::oldOtp($user->id)->first();
            // dd($otpInfo);
            if ($otpInfo) {
                return errorResponse([], "Please wait for a minute to request new OTP.", 422);
            }
            $otp_data = [
                "user_id" => $user->id,
                "otp" => create_otp(),
                "username" => $user->mobile,
            ];
            $otp = OtpList::create($otp_data);
            SendPasswordResetOTPJob::dispatch($user, $otp->otp);
            $data = [];
            if (app()->environment() != 'production') {
                $data = [
                    "otp" => $otp->otp, // remove in production environment
                ];
            }
            return successResponse(200, "Password resest OTP has been sent to your mobile.", $data);
        } catch (\Throwable$th) {
            report($th);
            dd($th);
            return errorResponse($th, "Something went wrong while sending password reset OTP.");
        }
    }

    public function check_otp(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "otp" => "required|string|digits:" . OTP_SIZE,
        ]);
        if ($validation->fails()) {
            return validateFormData($validation);
        }
    }
    public function reset_password(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "mobile" => "required|string|min:10|max:18|exists:users,mobile",
            "otp" => "required|string|digits:" . OTP_SIZE,
            "new_password" => "required|string|min:8|max:32|same:confirm_password",

        ]);
        if ($validation->fails()) {
            return validateFormData($validation);
        }
        try {
            $user = User::where("mobile", $request->mobile)->first();
            if (!$user) {
                return errorResponse([], "Account does not exist for this mobile number.", 404);
            }
            $otpInfo = OtpList::whereOtp($request->mobile, $request->otp)->first();
            if (!$otpInfo) {
                return formErrorResponse(null, "Invalid OTP", 404);
            }
            if ($otpInfo->created_at < now()->subDays(5)) {
                return formErrorResponse(null, "OTP expired", 422);
            }
            DB::beginTransaction();
            $user->update([
                "password" => Hash::make($request->new_password),
            ]);
            $otpInfo->delete();
            DB::commit();
            return successResponse(200, "New password has been successfully updated.");
        } catch (\Throwable$th) {
            DB::rollback();
            report($th);
            return errorResponse($th, "Something went wrong while updating new password.");
        }
    }

    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "username" => "required|string",
            "password" => "required|string|min:8|max:30",
        ]);
        if ($validation->fails()) {
            return validateFormData($validation);
        }
        try {
            //code...
            $user = User::where(function ($query) use ($request) {
                return $query->where("mobile", $request->username)
                    ->orWhere("email", $request->username);
            })->first();

            if (!$user) {
                return errorResponse(null, "Invalid login credentials", 422);
            }
            if (!Hash::check($request->password, $user->password)) {
                return errorResponse(null, "Invalid login credentials", 422);
            }
            $token = $user->createToken('Personal Access Token')->plainTextToken;
            $data = [
                "token" => $token,
                'user' => $user,
            ];
            return successResponse(200, "Successfully logged in", $data);

        } catch (\Throwable$th) {
            report($th);
            return errorResponse($th, "Something went wrong while authenticating.");
        }
    }
}
