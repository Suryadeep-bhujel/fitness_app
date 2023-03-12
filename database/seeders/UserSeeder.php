<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    protected function create_random_date()
    {
        $startDate = Carbon::parse('2001-01-01 00:00:00');
        $endDate = Carbon::parse('2022-12-31 23:59:59');

        $randomTimestamp = mt_rand($startDate->timestamp, $endDate->timestamp);

        $randomDate = Carbon::createFromTimestamp($randomTimestamp);
        return $randomDate;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // dd($this->create_random_date());
        $faker = Faker::create();
        // $phoneNumber = $faker->e164PhoneNumber;
        // dd($phoneNumber);
        $data = [];
        $genders = ["Male", "Female", "Other"];
        $imageUrl = $faker->imageUrl($width = 640, $height = 480);

        foreach (range(0, 100) as $range) {
            $data[] = [
                "firstName" => $faker->firstName,
                "middleName" => $faker->optional()->firstName,
                "lastName" => $faker->lastName,
                "email" => $faker->email,
                "profilPhoto" => $imageUrl,
                "password" => Hash::make("fitness@4345"),
                "mobile" => $faker->e164PhoneNumber,
                "dateOfBirth" => $this->create_random_date(),
                "gender" => $genders[array_rand($genders)],
                "email_verified_at" => now(),
                "mobile_verified_at" => now(),
            ];
        }
        User::insert($data);
    }

}
