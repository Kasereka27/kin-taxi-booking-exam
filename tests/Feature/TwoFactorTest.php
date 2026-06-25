<?php

use App\Mail\TwoFactorOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function loginWithTwoFactor(User $user, string $password = 'password123'): string
{
    Mail::fake();

    test()->post(route('login.store'), [
        'email' => $user->email,
        'password' => $password,
    ])->assertRedirect(route('two-factor.show'));

    test()->assertGuest();

    $code = null;

    Mail::assertQueued(TwoFactorOtpMail::class, function (TwoFactorOtpMail $mail) use (&$code) {
        $code = $mail->code;

        return true;
    });

    expect($code)->not->toBeNull();

    return $code;
}

it('redirige vers la vérification OTP quand le 2FA est activé', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'two_factor_enabled' => true,
    ]);

    $code = loginWithTwoFactor($user);

    test()->post(route('two-factor.store'), ['code' => $code])
        ->assertRedirect(route('user.dashboardClient'));

    test()->assertAuthenticatedAs($user);
});

it('refuse un code OTP invalide', function () {
    $user = User::factory()->withTwoFactor()->create([
        'password' => Hash::make('password123'),
    ]);

    loginWithTwoFactor($user);

    test()->post(route('two-factor.store'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    test()->assertGuest();
});

it('refuse un code OTP expiré', function () {
    $user = User::factory()->withTwoFactor()->create([
        'password' => Hash::make('password123'),
    ]);

    $code = loginWithTwoFactor($user);

    $this->travel(config('two_factor.expires_minutes') + 1)->minutes();

    test()->post(route('two-factor.store'), ['code' => $code])
        ->assertSessionHasErrors('code');

    test()->assertGuest();
});

it('permet de renvoyer un code OTP', function () {
    Mail::fake();

    $user = User::factory()->withTwoFactor()->create([
        'password' => Hash::make('password123'),
    ]);

    loginWithTwoFactor($user);

    Mail::fake();

    test()->post(route('two-factor.resend'))
        ->assertRedirect(route('two-factor.show'))
        ->assertSessionHas('status');

    Mail::assertQueued(TwoFactorOtpMail::class);
});

it('permet d activer et désactiver le 2FA depuis le profil', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'two_factor_enabled' => false,
    ]);

    $this->actingAs($user)
        ->patch(route('profile.two-factor'), [
            'two_factor_enabled' => true,
            'current_password' => 'password123',
        ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('success');

    expect($user->fresh()->two_factor_enabled)->toBeTrue();

    $this->actingAs($user)
        ->patch(route('profile.two-factor'), [
            'two_factor_enabled' => false,
            'current_password' => 'password123',
        ])
        ->assertSessionHas('success');

    expect($user->fresh()->two_factor_enabled)->toBeFalse();
});

it('connecte normalement un utilisateur sans 2FA', function () {
    Mail::fake();

    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'two_factor_enabled' => false,
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertRedirect(route('user.dashboardClient'));

    $this->assertAuthenticatedAs($user);
    Mail::assertNothingQueued();
});

it('renvoie vers la connexion si la session OTP est absente', function () {
    $this->get(route('two-factor.show'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');
});
