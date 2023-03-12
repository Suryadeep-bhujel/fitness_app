<?php

namespace App\Models\Fitness;

use App\Models\User;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Jenssegers\Mongodb\Eloquent\Model;

class HealthRecord extends Model
{
    use HybridRelations;
    protected $connection = "mongodb";
    protected $collection = "health_records";
    protected $casts = [
        "start_time" => "datetime",
        "end_time" => "datetime",
    ];
    protected $dates = ["start_time", "end_time"];
    protected $fillable = [
        "user_id",
        "value",
        "start_time",
        "end_time",
        "record_type",
    ];
    public function user()
    {
        return $this->belongsTo(User::class,"user_id", "id");
    }

}
