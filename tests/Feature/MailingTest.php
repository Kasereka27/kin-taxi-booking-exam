<?php

use App\Mail\PaymentConfirmedMail;
use App\Mail\RideBookedMail;
use App\Mail\RideStatusMail;
use App\Mail\VerifyEmailMail;
use App\Mail\WelcomeMail;
use App\Models\Payment;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Contracts\Provider;

uses(RefreshDatabase::class);

it('envoie les e-mails de bienvenue et de vérification à l inscription', function () {
    Mail::fake();

    $this->post(route('register.store'), [
        'firstname' => 'Jean',
        'lastname' => 'Dupont',
        'email' => 'jean@exemple.com',
        'phone' => '0991234567',
        'role' => 'client',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('user.dashboardClient'));

    Mail::assertQueued(WelcomeMail::class, fn (WelcomeMail $mail) => $mail->user->email === 'jean@exemple.com');
    Mail::assertQueued(VerifyEmailMail::class, fn (VerifyEmailMail $mail) => $mail->user->email === 'jean@exemple.com');
});

it('envoie un e-mail de bienvenue lors de la première connexion Google', function () {
    Mail::fake();
    config(['services.google.client_id' => 'test-client-id']);

    $googleUser = Mockery::mock(Laravel\Socialite\Contracts\User::class);
    $googleUser->shouldReceive('getId')->andReturn('google-welcome-1');
    $googleUser->shouldReceive('getEmail')->andReturn('google-welcome@exemple.com');
    $googleUser->shouldReceive('getName')->andReturn('Paul Google');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($googleUser);

    $socialite = Mockery::mock(Factory::class);
    $socialite->shouldReceive('driver')->with('google')->andReturn($provider);
    $this->app->instance(Factory::class, $socialite);

    $this->get(route('auth.google.callback'))->assertRedirect(route('user.dashboardClient'));

    Mail::assertQueued(WelcomeMail::class, fn (WelcomeMail $mail) => $mail->user->email === 'google-welcome@exemple.com');
    Mail::assertNotQueued(VerifyEmailMail::class);
});

it('confirme l adresse e-mail via le lien signé', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect(route('user.dashboardClient'))
        ->assertSessionHas('success');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('envoie un e-mail de confirmation lors de la réservation d une course', function () {
    Mail::fake();

    $client = User::factory()->create();

    $this->actingAs($client)->post(route('rides.store'), [
        'pickup_addr' => 'Gare Centrale, Kinshasa',
        'dropoff_addr' => 'Aéroport de N\'djili',
        'vehicle_type' => 'eco',
    ])->assertRedirect();

    Mail::assertQueued(RideBookedMail::class, fn (RideBookedMail $mail) => $mail->ride->client_id === $client->id);
});

it('envoie un e-mail de notification quand un chauffeur accepte une course', function () {
    Mail::fake();

    $client = User::factory()->create();
    $driver = User::factory()->driver()->create();
    $ride = Ride::factory()->pending()->create(['client_id' => $client->id]);

    $this->actingAs($driver)
        ->patch(route('rides.accept', $ride))
        ->assertRedirect();

    Mail::assertQueued(RideStatusMail::class, function (RideStatusMail $mail) use ($ride) {
        return $mail->event === 'accepted' && $mail->ride->is($ride);
    });
});

it('envoie un e-mail de notification quand une course est annulée', function () {
    Mail::fake();

    $client = User::factory()->create();
    $driver = User::factory()->driver()->create();
    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'driver_id' => $driver->id,
        'status' => 'assigned',
    ]);

    $this->actingAs($client)
        ->patch(route('rides.cancel', $ride))
        ->assertRedirect();

    Mail::assertQueued(RideStatusMail::class, function (RideStatusMail $mail) use ($ride, $client) {
        return $mail->event === 'cancelled'
            && $mail->ride->is($ride)
            && $mail->actor?->is($client);
    });
});

it('envoie un e-mail de confirmation de paiement réussi', function () {
    Mail::fake();

    $client = User::factory()->create();
    $ride = Ride::factory()->create(['client_id' => $client->id, 'status' => 'completed']);
    Payment::create([
        'ride_id' => $ride->id,
        'user_id' => $client->id,
        'order_number' => 'DEP-MAIL-OK',
        'method' => 'mpesa',
        'amount' => $ride->price,
        'currency' => 'CDF',
        'status' => 'pending',
    ]);

    $this->postJson(route('labyrinthe.callback'), [
        'reference' => 'DEP-MAIL-OK',
        'results' => ['status' => ['code' => 2, 'name' => 'Success']],
    ])->assertOk();

    Mail::assertQueued(PaymentConfirmedMail::class, fn (PaymentConfirmedMail $mail) => $mail->payment->order_number === 'DEP-MAIL-OK');
});

it('permet de renvoyer l e-mail de vérification depuis le profil', function () {
    Mail::fake();

    $user = User::factory()->unverified()->create([
        'password' => Hash::make('password123'),
    ]);

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect()
        ->assertSessionHas('status');

    Mail::assertQueued(VerifyEmailMail::class, fn (VerifyEmailMail $mail) => $mail->user->is($user));
});
