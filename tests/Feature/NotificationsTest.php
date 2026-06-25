<?php

use App\Models\DriverProfile;
use App\Models\Payment;
use App\Models\Ride;
use App\Models\User;
use App\Notifications\NewRideAvailable;
use App\Notifications\PaymentFailed;
use App\Notifications\PaymentSucceeded;
use App\Notifications\RideAccepted;
use App\Notifications\RideCancelled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('notifie le client quand un chauffeur accepte sa course', function () {
    Notification::fake();

    $client = User::factory()->create();
    $driver = User::factory()->driver()->create();
    $ride = Ride::factory()->pending()->create(['client_id' => $client->id]);

    $this->actingAs($driver)
        ->patch(route('rides.accept', $ride))
        ->assertRedirect();

    Notification::assertSentTo($client, RideAccepted::class);
});

it('notifie le chauffeur quand le client annule la course', function () {
    Notification::fake();

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

    Notification::assertSentTo($driver, RideCancelled::class);
    Notification::assertNotSentTo($client, RideCancelled::class);
});

it('notifie les chauffeurs en ligne d’une nouvelle course', function () {
    Notification::fake();

    $client = User::factory()->create();

    $onlineDriver = User::factory()->driver()->create();
    DriverProfile::factory()->online()->create(['user_id' => $onlineDriver->id]);

    $offlineDriver = User::factory()->driver()->create();
    DriverProfile::factory()->create(['user_id' => $offlineDriver->id, 'is_online' => false]);

    $this->actingAs($client)
        ->post(route('rides.store'), [
            ...validRideAddressPayload(),
            'vehicle_type' => 'eco',
        ])
        ->assertRedirect();

    Notification::assertSentTo($onlineDriver, NewRideAvailable::class);
    Notification::assertNotSentTo($offlineDriver, NewRideAvailable::class);
});

it('notifie le client quand son paiement réussit (webhook)', function () {
    Notification::fake();

    $client = User::factory()->create();
    $ride = Ride::factory()->create(['client_id' => $client->id, 'status' => 'completed']);
    Payment::create([
        'ride_id' => $ride->id,
        'user_id' => $client->id,
        'order_number' => 'DEP-NOTIF-OK',
        'method' => 'mpesa',
        'amount' => $ride->price,
        'currency' => 'CDF',
        'status' => 'pending',
    ]);

    $this->postJson(route('labyrinthe.callback'), [
        'reference' => 'DEP-NOTIF-OK',
        'results' => ['status' => ['code' => 2, 'name' => 'Success']],
    ])->assertOk();

    Notification::assertSentTo($client, PaymentSucceeded::class);
});

it('notifie le client quand son paiement est refusé pour expiration', function () {
    Notification::fake();
    config()->set('labyrinthe.payment_timeout', 90);

    Http::fake([
        'payment.labyrinthe-rdc.com/*' => Http::response([
            'success' => true,
            'results' => ['status' => ['code' => 0, 'name' => 'Pending']],
        ], 200),
    ]);

    $client = User::factory()->create();
    $ride = Ride::factory()->create(['client_id' => $client->id, 'status' => 'completed']);
    $payment = Payment::create([
        'ride_id' => $ride->id,
        'user_id' => $client->id,
        'order_number' => 'DEP-NOTIF-KO',
        'provider_reference' => 'LAB-NOTIF-KO',
        'method' => 'mpesa',
        'amount' => $ride->price,
        'currency' => 'CDF',
        'status' => 'pending',
    ]);
    $payment->forceFill(['created_at' => now()->subMinutes(5)])->save();

    $this->actingAs($client)
        ->getJson(route('payments.poll', $payment))
        ->assertOk();

    Notification::assertSentTo($client, PaymentFailed::class);
});

it('affiche la page des notifications', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->create(['client_id' => $client->id]);
    $client->notify(new RideAccepted($ride));

    $this->actingAs($client)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Notifications');
});

it('marque une notification comme lue et redirige vers sa cible', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->create(['client_id' => $client->id]);
    $client->notify(new RideAccepted($ride));
    $notification = $client->notifications()->firstOrFail();

    $this->actingAs($client)
        ->post(route('notifications.read', $notification->id))
        ->assertRedirect(route('rides.show', $ride));

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('marque toutes les notifications comme lues', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->create(['client_id' => $client->id]);
    $client->notify(new RideAccepted($ride));
    $client->notify(new RideAccepted($ride));

    expect($client->unreadNotifications()->count())->toBe(2);

    $this->actingAs($client)
        ->post(route('notifications.readAll'))
        ->assertRedirect();

    expect($client->fresh()->unreadNotifications()->count())->toBe(0);
});
