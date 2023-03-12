<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpList extends Model
{
    protected $fillable = [
        "user_id",
        "otp",
        "username",
    ];
    public function scopeOldOtp($query, $user_id)
    {

        return $query->where("user_id", $user_id)->where("created_at", ">=", now()->subMinutes(1))->latest();
    }
    public function scopeWhereOTP($query, $mobile, $otp)
    {
        return $query->where('otp', $otp)->where("username", $mobile);
    }
}
