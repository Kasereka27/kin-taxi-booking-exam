<?php

use App\Events\RideTrackingUpdated;
use App\Models\DriverProfile;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('autorise le client à écouter le canal de suivi', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'status' => 'approche',
    ]);

    expect($client->can('view', $ride))->toBeTrue();
});

it('refuse un utilisateur non lié à la course sur le canal de suivi', function () {
    $ride = Ride::factory()->create(['status' => 'approche']);
    $stranger = User::factory()->create();

    expect($stranger->can('view', $ride))->toBeFalse();
});

it('permet au chauffeur assigné de publier sa position', function () {
    Event::fake([RideTrackingUpdated::class]);

    $driver = User::factory()->driver()->create();
    DriverProfile::factory()->create(['user_id' => $driver->id]);
    $ride = Ride::factory()->create([
        'driver_id' => $driver->id,
        'status' => 'assigned',
        'pickup_lat' => -4.3217,
        'pickup_lng' => 15.3125,
        'dropoff_lat' => -4.3017,
        'dropoff_lng' => 15.3325,
    ]);

    $this->actingAs($driver)
        ->patchJson(route('rides.tracking', $ride), [
            'lat' => -4.3200,
            'lng' => 15.3130,
        ])
        ->assertOk()
        ->assertJsonPath('status', 'approche');

    Event::assertDispatched(RideTrackingUpdated::class, function (RideTrackingUpdated $event) use ($ride) {
        return $event->ride->id === $ride->id;
    });

    expect((float) $driver->driverProfile->fresh()->current_lat)->toBe(-4.32);
});

it('refuse la mise à jour GPS à un client', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'status' => 'approche',
    ]);

    $this->actingAs($client)
        ->patchJson(route('rides.tracking', $ride), [
            'lat' => -4.32,
            'lng' => 15.31,
        ])
        ->assertForbidden();
});

it('initialise le suivi lors de l\'acceptation d\'une course', function () {
    Event::fake([RideTrackingUpdated::class]);

    $driver = User::factory()->driver()->create();
    DriverProfile::factory()->create(['user_id' => $driver->id]);
    $ride = Ride::factory()->pending()->create();

    $this->actingAs($driver)
        ->patch(route('rides.accept', $ride))
        ->assertRedirect();

    Event::assertDispatched(RideTrackingUpdated::class);
    expect($ride->fresh()->status)->toBe('assigned');
    expect($driver->driverProfile->fresh()->current_lat)->not->toBeNull();
});

it('expose la position actuelle du chauffeur au client', function () {
    $client = User::factory()->create();
    $driver = User::factory()->driver()->create();
    DriverProfile::factory()->create([
        'user_id' => $driver->id,
        'current_lat' => -4.32,
        'current_lng' => 15.31,
    ]);
    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'driver_id' => $driver->id,
        'status' => 'approche',
    ]);

    $this->actingAs($client)
        ->getJson(route('rides.tracking.show', $ride))
        ->assertOk()
        ->assertJsonPath('lat', -4.32)
        ->assertJsonPath('lng', 15.31)
        ->assertJsonPath('status', 'approche');
});

it('refuse la consultation GPS à un utilisateur non autorisé', function () {
    $ride = Ride::factory()->create(['status' => 'approche']);
    $stranger = User::factory()->create();

    $this->actingAs($stranger)
        ->getJson(route('rides.tracking.show', $ride))
        ->assertForbidden();
});
