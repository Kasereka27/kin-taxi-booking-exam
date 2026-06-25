<?php

use App\Models\Ride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('exige une authentification pour lister les courses', function () {
    $this->get(route('rides.index'))->assertRedirect(route('login'));
});

it('permet à un client de créer une course avec des coordonnées Kinshasa', function () {
    $client = User::factory()->create();

    $response = $this->actingAs($client)->post(route('rides.store'), [
        ...validRideAddressPayload(),
        'vehicleType' => 'confort',
    ]);

    $response->assertRedirect(route('user.dashboardClient'));

    $ride = Ride::first();
    expect($ride)->not->toBeNull()
        ->and($ride->client_id)->toBe($client->id)
        ->and($ride->status)->toBe('pending')
        ->and($ride->vehicle_type)->toBe('confort')
        ->and((float) $ride->pickup_lat)->toBe(-4.3217)
        ->and((float) $ride->distance_km)->toBeGreaterThan(0)
        ->and((float) $ride->price)->toBeGreaterThan(0);
});

it('valide les champs obligatoires à la création', function () {
    $client = User::factory()->create();

    $this->actingAs($client)
        ->post(route('rides.store'), ['pickup_addr' => '', 'dropoff_addr' => '', 'vehicleType' => 'fusee'])
        ->assertSessionHasErrors(['pickup_addr', 'dropoff_addr', 'vehicle_type', 'pickup_lat', 'dropoff_lat']);
});

it('refuse une course sans coordonnées géographiques', function () {
    $client = User::factory()->create();

    $this->actingAs($client)
        ->post(route('rides.store'), [
            'pickup_addr' => 'Adresse inventée',
            'dropoff_addr' => 'Autre adresse inventée',
            'vehicleType' => 'eco',
        ])
        ->assertSessionHasErrors(['pickup_lat', 'dropoff_lat']);
});

it('refuse des coordonnées hors zone Kinshasa', function () {
    $client = User::factory()->create();

    $this->actingAs($client)
        ->post(route('rides.store'), [
            ...validRideAddressPayload([
                'pickup_lat' => -1.0,
                'pickup_lng' => 15.3125,
            ]),
            'vehicleType' => 'eco',
        ])
        ->assertSessionHasErrors(['pickup_lat']);
});

it('calcule la distance réelle entre deux points', function () {
    $distance = Ride::distanceKmBetween(-4.3217, 15.3125, -4.3858, 15.4446);

    expect($distance)->toBeGreaterThan(10)
        ->and($distance)->toBeLessThan(25);
});

it('ne liste que les courses du client connecté', function () {
    $client = User::factory()->create();
    $other = User::factory()->create();

    Ride::factory()->create(['client_id' => $client->id, 'pickup_addr' => 'Mienne']);
    Ride::factory()->create(['client_id' => $other->id, 'pickup_addr' => 'Autre']);

    $this->actingAs($client)
        ->get(route('rides.index'))
        ->assertOk()
        ->assertSee('Mienne')
        ->assertDontSee('Autre');
});

it('filtre les courses par statut', function () {
    $client = User::factory()->create();
    Ride::factory()->create(['client_id' => $client->id, 'status' => 'completed', 'pickup_addr' => 'Terminee']);
    Ride::factory()->pending()->create(['client_id' => $client->id, 'pickup_addr' => 'EnAttente']);

    $this->actingAs($client)
        ->get(route('rides.index', ['status' => 'pending']))
        ->assertSee('EnAttente')
        ->assertDontSee('Terminee');
});

it('permet au client d\'annuler sa course en attente', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->pending()->create(['client_id' => $client->id]);

    $this->actingAs($client)
        ->patch(route('rides.cancel', $ride))
        ->assertRedirect(route('rides.index'));

    expect($ride->fresh()->status)->toBe('cancelled');
});

it('empêche un client de voir la course d\'un autre', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->create(['client_id' => User::factory()->create()->id]);

    $this->actingAs($client)
        ->get(route('rides.show', $ride))
        ->assertForbidden();
});

it('permet de supprimer une course en attente', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->pending()->create(['client_id' => $client->id]);

    $this->actingAs($client)
        ->delete(route('rides.destroy', $ride))
        ->assertRedirect(route('rides.index'));

    expect(Ride::find($ride->id))->toBeNull();
});

it('autorise l\'administrateur à voir n\'importe quelle course', function () {
    $admin = User::factory()->admin()->create();
    $ride = Ride::factory()->create();

    $this->actingAs($admin)
        ->get(route('rides.show', $ride))
        ->assertOk();
});
