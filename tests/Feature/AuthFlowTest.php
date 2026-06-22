<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('affiche la page de connexion aux invités', function () {
    $this->get(route('login'))->assertOk();
});

it('inscrit un client et le redirige vers son dashboard', function () {
    $response = $this->post(route('register.store'), [
        'firstname' => 'Jean',
        'lastname' => 'Dupont',
        'email' => 'jean@exemple.com',
        'phone' => '0991234567',
        'role' => 'client',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('user.dashboardClient'));
    $this->assertAuthenticated();
    expect(User::where('email', 'jean@exemple.com')->exists())->toBeTrue();
});

it('connecte un chauffeur et le redirige vers son dashboard', function () {
    $driver = User::factory()->driver()->create([
        'password' => Hash::make('password123'),
    ]);

    $this->post(route('login.store'), [
        'email' => $driver->email,
        'password' => 'password123',
    ])->assertRedirect(route('user.dashboardDriver'));

    $this->assertAuthenticatedAs($driver);
});

it('refuse une connexion avec de mauvais identifiants', function () {
    $user = User::factory()->create(['password' => Hash::make('password123')]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'mauvais',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('empêche un compte désactivé de se connecter', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'is_active' => false,
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('interdit à un client d\'accéder au dashboard chauffeur', function () {
    $client = User::factory()->create();

    $this->actingAs($client)
        ->get(route('user.dashboardDriver'))
        ->assertForbidden();
});

it('autorise un admin à accéder à son dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('redirige un utilisateur connecté hors de la page de connexion', function () {
    $client = User::factory()->create();

    $this->actingAs($client)
        ->get(route('login'))
        ->assertRedirect(route('user.dashboardClient'));
});

it('déconnecte un utilisateur', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});
