<?php

use App\Models\Ride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function apiClient(): User
{
    return User::factory()->create([
        'password' => Hash::make('password123'),
    ]);
}

it('permet à un client de créer une course via l API', function () {
    Sanctum::actingAs(apiClient(), ['*']);

    $response = $this->postJson('/api/rides', [
        ...validRideAddressPayload(),
        'vehicle_type' => 'confort',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.ride.vehicle_type', 'confort')
        ->assertJsonPath('data.ride.status', 'pending');

    expect(Ride::count())->toBe(1);
});

it('liste les courses du client connecté', function () {
    $client = apiClient();
    $other = User::factory()->create();

    Ride::factory()->create(['client_id' => $client->id, 'pickup_addr' => 'MaCourse']);
    Ride::factory()->create(['client_id' => $other->id, 'pickup_addr' => 'AutreCourse']);

    Sanctum::actingAs($client, ['*']);

    $this->getJson('/api/rides')
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data.rides');
});

it('affiche le détail d une course autorisée', function () {
    $client = apiClient();
    $ride = Ride::factory()->create(['client_id' => $client->id]);

    Sanctum::actingAs($client, ['*']);

    $this->getJson("/api/rides/{$ride->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.ride.id', $ride->id);
});

it('annule une course via l API', function () {
    $client = apiClient();
    $ride = Ride::factory()->pending()->create(['client_id' => $client->id]);

    Sanctum::actingAs($client, ['*']);

    $this->patchJson("/api/rides/{$ride->id}/cancel")
        ->assertSuccessful()
        ->assertJsonPath('data.ride.status', 'cancelled');
});

it('supprime une course pending via l API', function () {
    $client = apiClient();
    $ride = Ride::factory()->pending()->create(['client_id' => $client->id]);

    Sanctum::actingAs($client, ['*']);

    $this->deleteJson("/api/rides/{$ride->id}")
        ->assertSuccessful();

    expect(Ride::find($ride->id))->toBeNull();
});

it('refuse l accès à la course d un autre client', function () {
    $client = apiClient();
    $ride = Ride::factory()->create(['client_id' => User::factory()->create()->id]);

    Sanctum::actingAs($client, ['*']);

    $this->getJson("/api/rides/{$ride->id}")->assertForbidden();
});

it('refuse la création de course par un chauffeur', function () {
    Sanctum::actingAs(User::factory()->driver()->create(), ['*']);

    $this->postJson('/api/rides', [
        ...validRideAddressPayload(),
        'vehicle_type' => 'eco',
    ])->assertForbidden();
});
