<?php

use App\Models\DriverProfile;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('expose les variables carte dans la page suivi', function () {
    $client = User::factory()->create();
    $driver = User::factory()->driver()->create();
    DriverProfile::factory()->create(['user_id' => $driver->id]);

    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'driver_id' => $driver->id,
        'status' => 'approche',
        'pickup_lat' => -4.3217,
        'pickup_lng' => 15.3125,
        'dropoff_lat' => -4.3017,
        'dropoff_lng' => 15.3325,
    ]);

    $this->actingAs($client)
        ->get(route('suivi.ride', $ride))
        ->assertOk()
        ->assertSee('trackingRide', false)
        ->assertSee((string) $ride->id)
        ->assertSee('Autoriser le suivi GPS')
        ->assertSee('tracking-consent-modal', false)
        ->assertSee('politique de confidentialité');
});

it('n\'affiche pas la modale de consentement sans chauffeur assigné', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->pending()->create(['client_id' => $client->id]);

    $this->actingAs($client)
        ->get(route('suivi.ride', $ride))
        ->assertOk()
        ->assertDontSee('Autoriser le suivi GPS');
});
