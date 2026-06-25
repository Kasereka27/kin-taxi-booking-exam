<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('retourne un token pour des identifiants valides', function () {
    $user = User::factory()->create([
        'email' => 'api@exemple.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'api@exemple.com',
        'password' => 'password123',
        'device_name' => 'test-device',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.user.email', 'api@exemple.com');

    expect($response->json('data.token'))->toBeString();
});

it('refuse une connexion API avec de mauvais identifiants', function () {
    User::factory()->create(['email' => 'api@exemple.com']);

    $this->postJson('/api/login', [
        'email' => 'api@exemple.com',
        'password' => 'wrong',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('success', false);
});

it('refuse une connexion API quand le 2FA est activé', function () {
    User::factory()->withTwoFactor()->create([
        'email' => '2fa@exemple.com',
        'password' => Hash::make('password123'),
    ]);

    $this->postJson('/api/login', [
        'email' => '2fa@exemple.com',
        'password' => 'password123',
    ])
        ->assertForbidden()
        ->assertJsonPath('success', false);
});

it('retourne le profil utilisateur avec un token valide', function () {
    $user = User::factory()->create();

    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertSuccessful()
        ->assertJsonPath('data.user.id', $user->id);
});

it('révoque le token lors de la déconnexion API', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $login = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertSuccessful();

    $token = $login->json('data.token');

    $this->withToken($token)
        ->postJson('/api/logout')
        ->assertSuccessful();

    $this->assertDatabaseCount('personal_access_tokens', 0);

    $this->app['auth']->forgetGuards();

    $this->withToken($token)
        ->getJson('/api/user')
        ->assertUnauthorized();
});

it('exige un token pour accéder aux routes protégées', function () {
    $this->getJson('/api/rides')->assertUnauthorized();
});
