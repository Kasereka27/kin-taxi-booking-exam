<?php

use App\Mail\ContactMessage;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('envoie un message de contact par e-mail', function () {
    Mail::fake();

    $response = $this->post(route('contact.store'), [
        'name' => 'Jean Dupont',
        'email' => 'jean@exemple.com',
        'subject' => 'general',
        'message' => 'Bonjour, j\'ai une question sur vos tarifs à Kinshasa.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertSent(ContactMessage::class, function (ContactMessage $mail) {
        return $mail->senderEmail === 'jean@exemple.com'
            && $mail->subjectLabel === 'Question générale';
    });
});

it('valide le formulaire de contact', function () {
    $this->post(route('contact.store'), [
        'name' => '',
        'email' => 'invalid',
        'subject' => 'unknown',
        'message' => 'court',
    ])
        ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

    Mail::fake();
    Mail::assertNothingSent();
});

it('affiche la course active du client sur la page suivi', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->pending()->create(['client_id' => $client->id]);

    $this->actingAs($client)
        ->get(route('suivi'))
        ->assertOk()
        ->assertSee($ride->reference())
        ->assertSee('Recherche d\'un chauffeur');
});

it('affiche le suivi d\'une course précise pour le client', function () {
    $client = User::factory()->create();
    $driver = User::factory()->driver()->create();
    $ride = Ride::factory()->create([
        'client_id' => $client->id,
        'driver_id' => $driver->id,
        'status' => 'approche',
    ]);

    $this->actingAs($client)
        ->get(route('suivi.ride', $ride))
        ->assertOk()
        ->assertSee($ride->reference())
        ->assertSee($driver->firstname);
});

it('refuse le suivi d\'une course d\'un autre client', function () {
    $client = User::factory()->create();
    $other = User::factory()->create();
    $ride = Ride::factory()->pending()->create(['client_id' => $other->id]);

    $this->actingAs($client)
        ->get(route('suivi.ride', $ride))
        ->assertForbidden();
});

it('affiche un état vide lorsqu\'aucune course n\'est active', function () {
    $client = User::factory()->create();

    $this->actingAs($client)
        ->get(route('suivi'))
        ->assertOk()
        ->assertSee('Aucune course à suivre');
});

it('permet au client d\'annuler sa course depuis le suivi', function () {
    $client = User::factory()->create();
    $ride = Ride::factory()->pending()->create(['client_id' => $client->id]);

    $this->actingAs($client)
        ->patch(route('rides.cancel', $ride))
        ->assertRedirect();

    expect($ride->fresh()->status)->toBe('cancelled');
});
