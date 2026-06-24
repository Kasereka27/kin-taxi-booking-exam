<?php

use App\Models\DriverProfile;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('affiche le dashboard chauffeur dynamique', function () {
    $driver = User::factory()->driver()->create();
    DriverProfile::factory()->online()->create(['user_id' => $driver->id]);

    Ride::factory()->create([
        'driver_id' => $driver->id,
        'status' => 'completed',
        'completed_at' => now(),
    ]);

    $this->actingAs($driver)
        ->get(route('user.dashboardDriver'))
        ->assertOk()
        ->assertSee('Revenus du jour')
        ->assertSee('Demandes de courses disponibles');
});

it('permet à un chauffeur d’accepter une course en attente', function () {
    $driver = User::factory()->driver()->create();
    $client = User::factory()->create();
    $ride = Ride::factory()->pending()->create(['client_id' => $client->id]);

    $this->actingAs($driver)
        ->patch(route('rides.accept', $ride))
        ->assertRedirect();

    $ride->refresh();
    expect($ride->driver_id)->toBe($driver->id);
    expect($ride->status)->toBe('assigned');
    expect($ride->accepted_at)->not->toBeNull();
    expect((float) $ride->price)->toBeGreaterThan(0);
});

it('refuse une course qui n’est plus disponible', function () {
    $driver = User::factory()->driver()->create();
    $otherDriver = User::factory()->driver()->create();
    $ride = Ride::factory()->create([
        'driver_id' => $otherDriver->id,
        'status' => 'assigned',
    ]);

    $this->actingAs($driver)
        ->patch(route('rides.accept', $ride))
        ->assertSessionHas('error');

    expect($ride->fresh()->driver_id)->toBe($otherDriver->id);
});

it('permet à un chauffeur de consulter le détail d’une demande en attente', function () {
    $driver = User::factory()->driver()->create();
    $ride = Ride::factory()->pending()->create();

    $this->actingAs($driver)
        ->get(route('rides.show', $ride))
        ->assertOk();
});

it('interdit à un chauffeur de consulter une course assignée à un autre chauffeur', function () {
    $driver = User::factory()->driver()->create();
    $otherDriver = User::factory()->driver()->create();
    $ride = Ride::factory()->create([
        'driver_id' => $otherDriver->id,
        'status' => 'assigned',
    ]);

    $this->actingAs($driver)
        ->get(route('rides.show', $ride))
        ->assertForbidden();
});

it('interdit à un client d’accepter une course', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->pending()->create();

    $this->actingAs($client)
        ->patch(route('rides.accept', $ride))
        ->assertForbidden();
});
