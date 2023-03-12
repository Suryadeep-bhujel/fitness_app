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
              
                HealthRecord::query()
                    ->where('start_time', $start_time)
                    ->where("end_time", $end_time)
                    ->where('user_id', auth()->id())
                    ->where("record_type", $request->record_type)
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

    public function leaderboard(Request $request)
    {
        // dd($request->user());
        try {

            $today = Carbon::today();
            $date = new \MongoDB\BSON\UTCDateTime(Carbon::createFromFormat("Y-m-d", $today->toDateString())->startOfDay());

            $leaderboard = HealthRecord::query()
                ->select('id', 'user_id', 'value')
                ->with(["user" => function ($query) {
                    return $query->select('id', DB::raw("CONCAT_WS(' ', firstName, middleName, lastName) AS fullname"), "profilPhoto");
                }])
                ->whereDate("start_time", ">=", $date)
                ->orderBy("value", "DESC")
                ->limit(10)
                ->get();
            $top_three_leaderboard = $leaderboard->take(3);
            // $first = $top_three_leaderboard[0];
            // $top_three_leaderboard[0] = $top_three_leaderboard[1];
            // $top_three_leaderboard[1] = $first;
            // $leaderboard = $leaderboard->skip(3)->take(10);
          
            $data = [
                "leaderboard" => $leaderboard,
                // "top_three_leaderboard" => $top_three_leaderboard,
            ];
            return success200($data);
        } catch (\Throwable$th) {
            report($th);
            dd($th);
            return errorResponse($th, "Something went wrong while fetching leaderboard.");
        }

    }
}
