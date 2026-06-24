<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;

uses(RefreshDatabase::class);

it('affiche la page profil pour un utilisateur connecté', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee($user->email);
});

it('met à jour le profil utilisateur', function () {
    $user = User::factory()->create([
        'firstname' => 'Jean',
        'lastname' => 'Dupont',
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'firstname' => 'Marie',
            'lastname' => 'Curie',
            'email' => 'marie@exemple.com',
            'phone' => '0998765432',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $user->refresh();
    expect($user->firstname)->toBe('Marie')
        ->and($user->email)->toBe('marie@exemple.com');
});

it('change le mot de passe du profil', function () {
    $user = User::factory()->create([
        'password' => Hash::make('ancien-mot-de-passe'),
    ]);

    $this->actingAs($user)
        ->patch(route('profile.password'), [
            'current_password' => 'ancien-mot-de-passe',
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Hash::check('nouveau-mot-de-passe', $user->fresh()->password))->toBeTrue();
});

it('envoie un lien de réinitialisation de mot de passe', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), [
        'email' => $user->email,
    ])
        ->assertRedirect()
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

it('affiche les pages légales', function () {
    $this->get(route('legal.cgu'))->assertOk()->assertSee('Conditions générales');
    $this->get(route('legal.privacy'))->assertOk()->assertSee('Politique de confidentialité');
});

it('refuse la connexion Google si non configurée', function () {
    config([
        'services.google.client_id' => null,
        'services.google.client_secret' => null,
    ]);

    $this->get(route('auth.google.redirect'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');
});

it('connecte un nouvel utilisateur via Google', function () {
    config([
        'services.google.client_id' => 'test-client-id',
        'services.google.client_secret' => 'test-secret',
    ]);

    $googleUser = Mockery::mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn('google-123456');
    $googleUser->shouldReceive('getEmail')->andReturn('google@exemple.com');
    $googleUser->shouldReceive('getName')->andReturn('Paul Google');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($googleUser);

    $socialite = Mockery::mock(SocialiteFactory::class);
    $socialite->shouldReceive('driver')->with('google')->andReturn($provider);
    $this->app->instance(SocialiteFactory::class, $socialite);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('user.dashboardClient'));

    $this->assertAuthenticated();
    expect(User::where('email', 'google@exemple.com')->value('google_id'))->toBe('google-123456');
});

it('lie un compte existant lors de la connexion Google', function () {
    config(['services.google.client_id' => 'test-client-id']);

    $user = User::factory()->create(['email' => 'existant@exemple.com']);

    $googleUser = Mockery::mock(SocialiteUser::class);
    $googleUser->shouldReceive('getId')->andReturn('google-link-99');
    $googleUser->shouldReceive('getEmail')->andReturn('existant@exemple.com');
    $googleUser->shouldReceive('getName')->andReturn('Existant User');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($googleUser);

    $socialite = Mockery::mock(SocialiteFactory::class);
    $socialite->shouldReceive('driver')->with('google')->andReturn($provider);
    $this->app->instance(SocialiteFactory::class, $socialite);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('user.dashboardClient'));

    expect($user->fresh()->google_id)->toBe('google-link-99');
});
