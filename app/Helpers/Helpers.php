<?php
defined('OTP_SIZE') || define('OTP_SIZE', 6);
function create_otp()
{
    return rand(100000, 999999);
}
function validateFormData($validation)
{
    if ($validation->fails()) {
        return formErrorResponse(mapErrorMessage($validation), $validation->messages()->first());
    }

}
function mapErrorMessage($validation)
{
    $errors = [];
    // dd($validation->messages());
    foreach ($validation->errors()->all() as $key => $message) {
        $errors[] = $message;
    }
    return $errors;
}
function formErrorResponse($error, $message = null, $status_code = 422, $data = null)
{
    if (!$message) {
        $message = "Something went wrong";
    }
    return response()->json([
        "data" => $data ? $data : [],
        'success' => false,
        "status_code" => $status_code,
        "message" => $message,
        "errors" => $error  ? $error : [],
    ], $status_code)
        ->withHeaders(['Content-Type' => "application/json"]);
}
function successResponse(int $status_code = 200, string $message = null, $data = null)
{
    return response()->json([
        "data" => $data,
        'success' => true,
        "status_code" => $status_code,
        "message" => $message,
        "errors" => [],
    ], $status_code)
        ->withHeaders(['Content-Type' => "application/json"]);
}
function success200($data)
{
    return response()->json([
        "data" => $data,
        'success' => true,
        "status_code" => 200,
        "errors" => [],
    ], 200)
        ->withHeaders(['Content-Type' => "application/json"]);
}
function errorResponse($error, $message = null, $status_code = 500, $data = null)
{
    if (!$message) {
        $message = "Something went wrong";
    }
    return response()->json([
        "data" => $data,
        'success' => false,
        "status_code" => $status_code,
        "message" => $message,
        "errors" => $error,
    ], $status_code)
        ->withHeaders(['Content-Type' => "application/json"]);
}
