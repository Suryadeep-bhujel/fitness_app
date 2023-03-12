<?php

namespace App\Http\Controllers;

use App\Models\Fitness\HealthRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HealthRecordController extends Controller
{
    public function update_health_records(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "record_type" => "required|string|in:step_count,sleep_score",
            "records.*.stepCount" => "required|numeric",
            "records.*.start_time" => "required",
            "records.*.end_time" => "required",
        ]);
        if ($validation->fails()) {
            return validateFormData($validation);
        }
        try {
            //code...
            DB::beginTransaction();
            @set_time_limit(0);
            foreach ($request->records as $recordItem) {
                // dd($recordItem);
                $start_time = Carbon::createFromFormat('Y-m-d H:i:s O', $recordItem['start_time']);
                $end_time = Carbon::createFromFormat('Y-m-d H:i:s O', $recordItem['end_time']);
                $data = [
                    "start_time" => $start_time,
                    "end_time" => $end_time,
                    "value" => doubleval($recordItem['stepCount']),
                    "user_id" => auth()->id(),
                    "record_type" => $request->record_type,
                ];
                // $date = Carbon::createFromFormat('Y-m-d H:i:s O', $recordItem['end_time']);
                // dd($date);
                $record = HealthRecord::query()
                    ->where('start_time', $start_time)
                    ->where("end_time", $end_time)
                    ->updateOrCreate($data);

            }
            DB::commit();
            return successResponse(201, "Your record has been updated successfully.");
        } catch (\Throwable$th) {
            DB::rollback();
            report($th);
            return errorResponse($th, "Something went wrong while updating Health records data.");
        }

    }
}
