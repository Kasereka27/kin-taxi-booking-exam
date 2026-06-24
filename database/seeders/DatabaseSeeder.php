<?php

namespace Database\Seeders;

use App\Models\DriverProfile;
use App\Models\Payment;
use App\Models\Rating;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'firstname' => 'Admin',
            'lastname' => 'KinTaxiBooking',
            'email' => 'admin@exemple.com',
            'phone' => '0990000001',
            'password' => Hash::make('password'),
        ]);

        $testDriver = User::factory()->driver()->create([
            'firstname' => 'Chauffeur',
            'lastname' => 'Test',
            'email' => 'chauffeur@exemple.com',
            'phone' => '0990000002',
            'password' => Hash::make('password'),
        ]);
        DriverProfile::factory()->online()->create(['user_id' => $testDriver->id]);

        $testClient = User::factory()->create([
            'firstname' => 'Client',
            'lastname' => 'Test',
            'email' => 'client@exemple.com',
            'phone' => '0990000003',
            'password' => Hash::make('password'),
        ]);

        $drivers = User::factory(7)->driver()->create();
        $drivers->each(fn (User $driver) => DriverProfile::factory()->create(['user_id' => $driver->id]));
        $drivers->push($testDriver);

        $clients = User::factory(14)->create();
        $clients->push($testClient);

        foreach (range(1, 30) as $ignored) {
            $client = $clients->random();
            $driver = $drivers->random();

            $ride = Ride::factory()->create([
                'client_id' => $client->id,
                'driver_id' => $driver->id,
            ]);

            Payment::factory()->create([
                'ride_id' => $ride->id,
                'user_id' => $client->id,
                'amount' => $ride->price,
            ]);

            Rating::factory()->create([
                'ride_id' => $ride->id,
                'from_user_id' => $client->id,
                'to_user_id' => $driver->id,
            ]);
        }

        // Courses terminées NON payées à petit montant (100 FC) pour tester le paiement Mobile Money.
        Ride::factory(4)->create([
            'client_id' => $testClient->id,
            'driver_id' => $drivers->random()->id,
            'price' => 100,
            'distance_km' => 1,
        ]);

        foreach (range(1, 6) as $ignored) {
            Ride::factory()->pending()->create([
                'client_id' => $clients->random()->id,
            ]);
        }

        foreach (range(1, 4) as $ignored) {
            Ride::factory()->cancelled()->create([
                'client_id' => $clients->random()->id,
                'driver_id' => $drivers->random()->id,
            ]);
        }
    }
}
