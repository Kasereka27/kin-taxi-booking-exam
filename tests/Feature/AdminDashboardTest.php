<?php

use App\Models\DriverProfile;
use App\Models\Payment;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('affiche le dashboard administrateur avec les statistiques et le graphique', function () {
    $admin = User::factory()->admin()->create();
    $driver = User::factory()->driver()->create();
    DriverProfile::factory()->online()->create(['user_id' => $driver->id]);
    $client = User::factory()->create();

    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'driver_id' => $driver->id,
    ]);
    Payment::factory()->create([
        'ride_id' => $ride->id,
        'user_id' => $client->id,
        'amount' => $ride->price,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Administration')
        ->assertSee('adminActivityChart')
        ->assertSee('FC');
});

it('interdit le dashboard administrateur aux clients', function () {
    $client = User::factory()->create();

    $this->actingAs($client)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('affiche la liste paginée des utilisateurs pour un admin', function () {
    $admin = User::factory()->admin()->create();
    User::factory(5)->create();

    $this->actingAs($admin)
        ->get(route('admin.users'))
        ->assertOk()
        ->assertSee('Utilisateurs');
});

it('filtre les utilisateurs par rôle', function () {
    $admin = User::factory()->admin()->create();
    $driver = User::factory()->driver()->create(['firstname' => 'ChauffeurUnique']);
    $client = User::factory()->create(['firstname' => 'ClientUnique']);

    $this->actingAs($admin)
        ->get(route('admin.users', ['role' => 'driver']))
        ->assertOk()
        ->assertSee('ChauffeurUnique')
        ->assertDontSee('ClientUnique');
});

it('permet à un admin de bloquer puis réactiver un client', function () {
    $admin = User::factory()->admin()->create();
    $client = User::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->patch(route('admin.users.toggle', $client))
        ->assertRedirect();

    expect($client->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->patch(route('admin.users.toggle', $client));

    expect($client->fresh()->is_active)->toBeTrue();
});

it('empêche de modifier le statut d’un autre administrateur', function () {
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->patch(route('admin.users.toggle', $otherAdmin))
        ->assertSessionHas('error');

    expect($otherAdmin->fresh()->is_active)->toBeTrue();
});

it('interdit la gestion des utilisateurs aux non-admins', function () {
    $client = User::factory()->create();

    $this->actingAs($client)
        ->get(route('admin.users'))
        ->assertForbidden();
});
