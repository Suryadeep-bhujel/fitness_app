<?php

namespace App\Models\Fitness;

use Jenssegers\Mongodb\Eloquent\Model;

class HealthRecord extends Model
{
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

}
