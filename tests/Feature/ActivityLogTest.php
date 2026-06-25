<?php

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('journalise une connexion web réussie', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertRedirect();

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $user->id,
        'action' => ActivityLogService::ACTION_LOGIN,
    ]);
});

it('journalise le blocage d\'un utilisateur par un admin', function () {
    $admin = User::factory()->admin()->create();
    $client = User::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->patch(route('admin.users.toggle', $client))
        ->assertRedirect();

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $admin->id,
        'action' => ActivityLogService::ACTION_USER_BLOCKED,
    ]);
});

it('affiche le journal d\'activité pour un administrateur', function () {
    $admin = User::factory()->admin()->create();

    ActivityLog::query()->create([
        'user_id' => $admin->id,
        'action' => ActivityLogService::ACTION_LOGIN,
        'description' => 'Connexion test.',
        'ip_address' => '127.0.0.1',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.activity-logs'))
        ->assertOk()
        ->assertSee('Journal d\'activité')
        ->assertSee('Connexion test.');
});

it('filtre le journal par type d\'action', function () {
    $admin = User::factory()->admin()->create();

    ActivityLog::query()->create([
        'user_id' => $admin->id,
        'action' => ActivityLogService::ACTION_LOGIN,
        'description' => 'Entrée visible',
    ]);

    ActivityLog::query()->create([
        'user_id' => $admin->id,
        'action' => ActivityLogService::ACTION_LOGOUT,
        'description' => 'Sortie cachée',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.activity-logs', ['action' => ActivityLogService::ACTION_LOGIN]))
        ->assertOk()
        ->assertSee('Entrée visible')
        ->assertDontSee('Sortie cachée');
});

it('interdit le journal d\'activité aux non-admins', function () {
    $client = User::factory()->create();

    $this->actingAs($client)
        ->get(route('admin.activity-logs'))
        ->assertForbidden();
});

it('paginate le journal d\'activité par lots de 8 entrées', function () {
    $admin = User::factory()->admin()->create();

    for ($i = 0; $i < 9; $i++) {
        ActivityLog::query()->create([
            'user_id' => $admin->id,
            'action' => ActivityLogService::ACTION_LOGIN,
            'description' => "Entrée de test {$i}",
        ]);
    }

    $this->actingAs($admin)
        ->get(route('admin.activity-logs'))
        ->assertOk()
        ->assertSee('Affichage de 1 à 8 sur 9 entrées')
        ->assertSee('Entrée de test 0')
        ->assertDontSee('Entrée de test 8');

    $this->actingAs($admin)
        ->get(route('admin.activity-logs', ['page' => 2]))
        ->assertOk()
        ->assertSee('Affichage de 9 à 9 sur 9 entrées')
        ->assertSee('Entrée de test 8');
});
