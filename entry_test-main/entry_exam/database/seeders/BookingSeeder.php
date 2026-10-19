<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // TRUNCATE DATABASE
        Schema::disableForeignKeyConstraints();
        DB::table('bookings')->truncate();
        Schema::enableForeignKeyConstraints();

        $faker = \Faker\Factory::create('ja_JP');
        $faker->seed(20241025);

        $hotelIds = DB::table('hotels')->pluck('hotel_id')->all();
        $today = CarbonImmutable::today();
        $bookings = [];

        for ($i = 0; $i < 200; $i++) {
            // check-in at 15:00, check-out at 10:00 after 1-5 nights
            $checkin = $today->addDays($faker->numberBetween(-60, 90))->setTime(15, 0);
            $checkout = $checkin->addDays($faker->numberBetween(1, 5))->setTime(10, 0);
            $createdAt = $checkin->subDays($faker->numberBetween(1, 60))->setTime($faker->numberBetween(0, 23), $faker->numberBetween(0, 59));

            $bookings[] = [
                'hotel_id' => $faker->randomElement($hotelIds),
                'customer_name' => $faker->name(),
                'customer_contact' => $faker->boolean() ? $faker->phoneNumber() : $faker->safeEmail(),
                'checkin_time' => $checkin,
                'checkout_time' => $checkout,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->addDays($faker->numberBetween(0, 3)),
            ];
        }

        // INSERT INTO DATABASE
        foreach (array_chunk($bookings, 100) as $chunk) {
            DB::table('bookings')->insert($chunk);
        }
    }
}
