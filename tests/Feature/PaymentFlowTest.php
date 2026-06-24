<?php

use App\Models\Payment;
use App\Models\Ride;
use App\Models\User;
use App\Services\LabyrinthePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('affiche le formulaire de paiement pour une course terminée du client', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'status' => 'completed',
    ]);

    $this->actingAs($client)
        ->get(route('rides.pay', $ride))
        ->assertOk()
        ->assertSee('Paiement Mobile Money');
});

it('refuse le paiement d’une course non terminée', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->pending()->create(['client_id' => $client->id]);

    $this->actingAs($client)
        ->get(route('rides.pay', $ride))
        ->assertForbidden();
});

it('refuse le paiement de la course d’un autre client', function () {
    $client = User::factory()->create();
    $other = User::factory()->create();
    $ride = Ride::factory()->create([
        'client_id' => $other->id,
        'status' => 'completed',
    ]);

    $this->actingAs($client)
        ->get(route('rides.pay', $ride))
        ->assertForbidden();
});

it('initie un dépôt et crée un paiement en attente', function () {
    Http::fake([
        'api.labyrinthe-rdc.com/*' => Http::response([
            'success' => true,
            'orderNumber' => 'LAB-REF-123',
            'message' => 'Transaction en cours',
            'results' => [
                'status' => ['code' => 0, 'name' => 'Pending'],
                'details' => ['provider' => ['name' => 'M-Pesa']],
            ],
        ], 200),
    ]);

    $client = User::factory()->create(['phone' => '0991234567']);
    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'status' => 'completed',
    ]);

    $response = $this->actingAs($client)->post(route('rides.pay.store', $ride), [
        'phone' => '0891234567',
        'method' => 'mpesa',
    ]);

    $payment = Payment::where('ride_id', $ride->id)->first();

    expect($payment)->not->toBeNull();
    expect($payment->status)->toBe('pending');
    expect($payment->method)->toBe('mpesa');
    expect($payment->provider_reference)->toBe('LAB-REF-123');

    $expectedFee = LabyrinthePaymentService::commissionFor((float) $ride->price);
    $expectedTotal = LabyrinthePaymentService::totalWithCommission((float) $ride->price);
    expect((float) $payment->fee)->toBe($expectedFee);
    expect((float) $payment->amount)->toBe($expectedTotal);

    Http::assertSent(fn ($request) => (float) $request['amount'] === $expectedTotal);

    $response->assertRedirect(route('payments.status', $payment));
});

it('inclut la commission Labyrinthe dans le total affiché au client', function () {
    config()->set('labyrinthe.commission_percent', 7);

    $client = User::factory()->create();
    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'status' => 'completed',
        'price' => 10000,
    ]);

    $this->actingAs($client)
        ->get(route('rides.pay', $ride))
        ->assertOk()
        ->assertSee('Frais Labyrinthe')
        ->assertSee('Total à payer');

    expect(LabyrinthePaymentService::commissionFor(10000.0))->toBe(700.0);
    expect(LabyrinthePaymentService::totalWithCommission(10000.0))->toBe(10700.0);
});

it('valide le format du numéro de téléphone', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'status' => 'completed',
    ]);

    $this->actingAs($client)
        ->post(route('rides.pay.store', $ride), [
            'phone' => '123',
            'method' => 'mpesa',
        ])
        ->assertSessionHasErrors('phone');

    expect(Payment::where('ride_id', $ride->id)->exists())->toBeFalse();
});

it('marque un paiement comme réussi via le webhook Labyrinthe', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'status' => 'completed',
    ]);
    $payment = Payment::create([
        'ride_id' => $ride->id,
        'user_id' => $client->id,
        'order_number' => 'DEP-TEST-1',
        'method' => 'mpesa',
        'amount' => $ride->price,
        'currency' => 'CDF',
        'status' => 'pending',
    ]);

    $this->postJson(route('labyrinthe.callback'), [
        'reference' => 'DEP-TEST-1',
        'results' => ['status' => ['code' => 2, 'name' => 'Success']],
    ])->assertOk();

    $payment->refresh();
    expect($payment->status)->toBe('success');
    expect($payment->paid_at)->not->toBeNull();
});

it('le suivi automatique renvoie le statut à jour du paiement', function () {
    Http::fake([
        'payment.labyrinthe-rdc.com/*' => Http::response([
            'success' => true,
            'results' => ['status' => ['code' => 2, 'name' => 'Success']],
        ], 200),
    ]);

    $client = User::factory()->create();
    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'status' => 'completed',
    ]);
    $payment = Payment::create([
        'ride_id' => $ride->id,
        'user_id' => $client->id,
        'order_number' => 'DEP-POLL-1',
        'provider_reference' => 'LAB-POLL-1',
        'method' => 'mpesa',
        'amount' => $ride->price,
        'currency' => 'CDF',
        'status' => 'pending',
    ]);

    $this->actingAs($client)
        ->getJson(route('payments.poll', $payment))
        ->assertOk()
        ->assertJson(['status' => 'success']);

    expect($payment->fresh()->status)->toBe('success');
});

it('refuse automatiquement un paiement non confirmé après le délai (code PIN non saisi)', function () {
    config()->set('labyrinthe.payment_timeout', 120);

    Http::fake([
        'payment.labyrinthe-rdc.com/*' => Http::response([
            'success' => true,
            'results' => ['status' => ['code' => 0, 'name' => 'Pending']],
        ], 200),
    ]);

    $client = User::factory()->create();
    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'status' => 'completed',
    ]);
    $payment = Payment::create([
        'ride_id' => $ride->id,
        'user_id' => $client->id,
        'order_number' => 'DEP-STALE-1',
        'provider_reference' => 'LAB-STALE-1',
        'method' => 'mpesa',
        'amount' => $ride->price,
        'currency' => 'CDF',
        'status' => 'pending',
    ]);
    $payment->forceFill(['created_at' => now()->subMinutes(5)])->save();

    $this->actingAs($client)
        ->getJson(route('payments.poll', $payment))
        ->assertOk()
        ->assertJson(['status' => 'failed']);

    $payment->refresh();
    expect($payment->status)->toBe('failed');
    expect($payment->failure_reason)->toBe('expired');
});

it('ne refuse pas un paiement en attente encore dans le délai', function () {
    config()->set('labyrinthe.payment_timeout', 120);

    Http::fake([
        'payment.labyrinthe-rdc.com/*' => Http::response([
            'success' => true,
            'results' => ['status' => ['code' => 0, 'name' => 'Pending']],
        ], 200),
    ]);

    $client = User::factory()->create();
    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'status' => 'completed',
    ]);
    $payment = Payment::create([
        'ride_id' => $ride->id,
        'user_id' => $client->id,
        'order_number' => 'DEP-FRESH-1',
        'provider_reference' => 'LAB-FRESH-1',
        'method' => 'mpesa',
        'amount' => $ride->price,
        'currency' => 'CDF',
        'status' => 'pending',
    ]);

    $this->actingAs($client)
        ->getJson(route('payments.poll', $payment))
        ->assertOk()
        ->assertJson(['status' => 'pending']);

    expect($payment->fresh()->status)->toBe('pending');
});

it('répond 404 au webhook pour une référence inconnue', function () {
    $this->postJson(route('labyrinthe.callback'), [
        'reference' => 'INCONNU',
        'results' => ['status' => ['code' => 2]],
    ])->assertNotFound();
});
