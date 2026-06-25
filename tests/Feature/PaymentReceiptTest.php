<?php

use App\Models\Payment;
use App\Models\Ride;
use App\Models\User;
use App\Services\PaymentReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('génère un reçu PDF quand le paiement réussit', function () {
    Storage::fake('public');

    $client = User::factory()->create();
    $ride = Ride::factory()->create(['client_id' => $client->id, 'status' => 'completed']);
    Payment::create([
        'ride_id' => $ride->id,
        'user_id' => $client->id,
        'order_number' => 'DEP-RECEIPT-1',
        'method' => 'mpesa',
        'amount' => $ride->price,
        'fee' => 500,
        'currency' => 'CDF',
        'status' => 'pending',
    ]);

    $this->postJson(route('labyrinthe.callback'), [
        'reference' => 'DEP-RECEIPT-1',
        'results' => ['status' => ['code' => 2, 'name' => 'Success']],
    ])->assertOk();

    $payment = Payment::where('order_number', 'DEP-RECEIPT-1')->firstOrFail();

    expect($payment->receipt_path)->not->toBeNull();
    Storage::disk('public')->assertExists($payment->receipt_path);
});

it('permet au client de télécharger son reçu PDF', function () {
    Storage::fake('public');

    $client = User::factory()->create();
    $ride = Ride::factory()->create(['client_id' => $client->id, 'status' => 'completed']);
    $payment = Payment::create([
        'ride_id' => $ride->id,
        'user_id' => $client->id,
        'order_number' => 'DEP-RECEIPT-2',
        'method' => 'airtel',
        'amount' => 15000,
        'fee' => 500,
        'currency' => 'CDF',
        'status' => 'success',
        'paid_at' => now(),
    ]);

    $this->actingAs($client)
        ->get(route('payments.receipt', $payment))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect($payment->fresh()->receipt_path)->not->toBeNull();
    Storage::disk('public')->assertExists($payment->fresh()->receipt_path);
});

it('refuse le téléchargement du reçu à un autre client', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $ride = Ride::factory()->create(['client_id' => $owner->id, 'status' => 'completed']);
    $payment = Payment::create([
        'ride_id' => $ride->id,
        'user_id' => $owner->id,
        'order_number' => 'DEP-RECEIPT-3',
        'method' => 'orange',
        'amount' => 12000,
        'currency' => 'CDF',
        'status' => 'success',
        'paid_at' => now(),
    ]);

    app(PaymentReceiptService::class)->generate($payment);

    $this->actingAs($intruder)
        ->get(route('payments.receipt', $payment))
        ->assertForbidden();
});

it('refuse le reçu pour un paiement non confirmé', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->create(['client_id' => $client->id]);
    $payment = Payment::create([
        'ride_id' => $ride->id,
        'user_id' => $client->id,
        'order_number' => 'DEP-RECEIPT-4',
        'method' => 'mpesa',
        'amount' => 10000,
        'currency' => 'CDF',
        'status' => 'pending',
    ]);

    $this->actingAs($client)
        ->get(route('payments.receipt', $payment))
        ->assertForbidden();
});

it('affiche le lien de reçu sur la page de statut payé', function () {
    Storage::fake('public');

    $client = User::factory()->create();
    $ride = Ride::factory()->create(['client_id' => $client->id, 'status' => 'completed']);
    $payment = Payment::create([
        'ride_id' => $ride->id,
        'user_id' => $client->id,
        'order_number' => 'DEP-RECEIPT-5',
        'method' => 'mpesa',
        'amount' => 18000,
        'currency' => 'CDF',
        'status' => 'success',
        'paid_at' => now(),
    ]);

    app(PaymentReceiptService::class)->generate($payment);

    $this->actingAs($client)
        ->get(route('payments.status', $payment))
        ->assertOk()
        ->assertSee('Télécharger le reçu PDF');
});
