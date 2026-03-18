<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DriverPerformanceSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $tenantId = 20;

        echo "Cleaning tenant data...\n";

        DB::table('advanced_rejected_blocks')->truncate();
        DB::table('rejected_blocks')->truncate();
        DB::table('rejected_loads')->truncate();
        DB::table('rejections')->where('tenant_id', $tenantId)->delete();
        DB::table('delays')->where('tenant_id', $tenantId)->delete();
        DB::table('safety_data')->where('tenant_id', $tenantId)->delete();
        DB::table('drivers')->where('tenant_id', $tenantId)->delete();

        echo "Creating drivers...\n";

        $drivers = [];

        for ($i = 1; $i <= 25; $i++) {

            $first = $faker->firstName;
            $last = $faker->lastName;

            $drivers[] = [

                'first_name' => $first,
                'last_name' => $last,
                'email' => strtolower($first . $i . '@fleetdemo.com'),
                'password' => Hash::make('password'),
                'mobile_phone' => $faker->phoneNumber,
                'tenant_id' => $tenantId,
                'netradyne_user_name' => strtolower($first . '.' . $last),
                'hiring_date' => Carbon::now()->subYears(rand(1, 5)),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('drivers')->insert($drivers);

        $driverNames = collect($drivers)
            ->map(fn($d) => strtolower($d['first_name'] . ' ' . $d['last_name']))
            ->values();

        $usernames = collect($drivers)
            ->map(fn($d) => $d['netradyne_user_name'])
            ->values();

        echo "Creating safety data...\n";

        foreach ($usernames as $index => $username) {

            for ($day = 0; $day < 60; $day++) {

                $date = Carbon::now()->subDays($day)->toDateString();

                DB::table('safety_data')->insert([

                    'tenant_id' => $tenantId,
                    'driver_name' => $driverNames[$index],
                    'user_name' => $username,

                    'minutes_analyzed' => rand(200, 700),
                    'driver_score' => rand(700, 980),

                    'traffic_light_violation' => rand(0, 2),
                    'speeding_violations' => rand(0, 3),
                    'following_distance' => rand(0, 2),
                    'roadside_parking' => rand(0, 1),
                    'driver_distraction' => rand(0, 2),
                    'sign_violations' => rand(0, 2),

                    'date' => $date,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        echo "Creating delays...\n";

        $delayCategories = ['1_60', '61_240', '241_600', '601_plus'];

        foreach ($driverNames as $driver) {

            for ($i = 0; $i < 30; $i++) {

                DB::table('delays')->insert([

                    'tenant_id' => $tenantId,

                    'date' => Carbon::now()->subDays(rand(0, 60)),

                    'delay_type' => rand(0, 1) ? 'origin' : 'destination',

                    'driver_name' => $driver,

                    'delay_category' => $delayCategories[array_rand($delayCategories)],

                    'penalty' => rand(0, 1),

                    'disputed' => 'none',

                    'driver_controllable' => rand(0, 1),

                    'carrier_controllable' => rand(0, 1),

                    'delay_duration' => rand(5, 400),

                    'delay_reason' => $faker->randomElement([
                        'traffic',
                        'weather',
                        'mechanical trailer',
                        'amazon delay'
                    ]),

                    'load_id' => rand(10000, 99999),

                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        echo "Creating rejections...\n";

        $rejectionId = 1;

        foreach ($driverNames as $driver) {

            for ($i = 0; $i < 10; $i++) {

                $rejection = DB::table('rejections')->insertGetId([

                    'tenant_id' => $tenantId,
                    'date' => Carbon::now()->subDays(rand(0, 60))->toDateString(),

                    'penalty' => 1,
                    'disputed' => 'none',

                    'carrier_controllable' => rand(0, 1),
                    'driver_controllable' => rand(0, 1),

                    'rejection_reason' => 'driver unavailable',

                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $type = rand(1, 3);

                if ($type === 1) {

                    DB::table('rejected_loads')->insert([

                        'rejection_id' => $rejection,

                        'load_id' => 'LD' . rand(10000, 99999),

                        'driver_name' => $driver,

                        'origin_yard_arrival' => Carbon::now()->subDays(rand(0, 60)),

                        'rejection_bucket' => 'rejected_after_start_time',

                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                } elseif ($type === 2) {

                    DB::table('rejected_blocks')->insert([

                        'rejection_id' => $rejection,

                        'block_id' => 'BL' . rand(10000, 99999),

                        'driver_name' => $driver,

                        'block_start' => Carbon::now()->subDays(rand(0, 60)),

                        'block_end' => Carbon::now()->subDays(rand(0, 50)),

                        'rejection_datetime' => Carbon::now()->subDays(rand(0, 60)),

                        'rejection_bucket' => 'less_than_24',

                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                } else {

                    DB::table('advanced_rejected_blocks')->insert([

                        'rejection_id' => $rejection,

                        'advance_block_rejection_id' => 'ADV-' . $rejectionId++,

                        'week_start' => Carbon::now()->subWeeks(rand(0, 8)),

                        'week_end' => Carbon::now()->subWeeks(rand(0, 7)),

                        'impacted_blocks' => rand(1, 3),

                        'expected_blocks' => rand(3, 6),

                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                }

            }
        }

        echo "Fleet demo dataset created successfully.\n";
    }
}
