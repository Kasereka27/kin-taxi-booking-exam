<?php

namespace App\Policies;

use App\Models\Ride;
use App\Models\User;

class RidePolicy
{
    /**
     * L'administrateur a tous les droits.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, Ride $ride): bool
    {
        return $ride->client_id === $user->id || $ride->driver_id === $user->id;
    }

    public function update(User $user, Ride $ride): bool
    {
        return $ride->client_id === $user->id;
    }

    public function delete(User $user, Ride $ride): bool
    {
        return $ride->client_id === $user->id && $ride->status === 'pending';
    }
}
