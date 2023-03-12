<?php

namespace Database\Seeders;

use App\Models\Fitness\HealthRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HealthRecordSeeder extends Seeder
{
    protected function generate_health_record($user_id)
    {
        $startDateTime = Carbon::now()->today()->setTime(0, 0, 0);
        $endDateTime = $startDateTime->copy()->addHours(23)->addMinutes(59)->addSeconds(59);
        $health_records = [
            "value" => rand(1, 100000),
            "record_type" => 'step_count',
            "start_time" => $startDateTime,
            "end_time" => $endDateTime,
            "user_id" => $user_id,
        ];
    
        HealthRecord::query()
            ->where('start_time', $startDateTime)
            ->where("end_time", $endDateTime)
            ->where('user_id', $user_id)
            ->where("record_type", "step_count")
            ->updateOrCreate($health_records);
        

    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        foreach (User::select("id")->get() as $user) {
            $this->generate_health_record($user->id);

        }
    }
}
