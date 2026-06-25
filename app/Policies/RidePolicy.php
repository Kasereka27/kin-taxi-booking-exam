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
        if ($ride->client_id === $user->id || $ride->driver_id === $user->id) {
            return true;
        }

        // Un chauffeur peut consulter une demande en attente non encore assignée.
        return $user->isDriver() && $ride->status === 'pending' && $ride->driver_id === null;
    }

    public function update(User $user, Ride $ride): bool
    {
        return $ride->client_id === $user->id;
    }

    public function delete(User $user, Ride $ride): bool
    {
        return $ride->client_id === $user->id && $ride->status === 'pending';
    }

    /**
     * Seul le client propriétaire peut régler une course terminée non encore payée.
     */
    public function pay(User $user, Ride $ride): bool
    {
        return $ride->client_id === $user->id && $ride->isPayable();
    }

    /**
     * Mise à jour GPS par le chauffeur assigné pendant une course active.
     */
    public function track(User $user, Ride $ride): bool
    {
        return $user->isDriver()
            && $ride->driver_id === $user->id
            && $ride->isTrackable();
    }
}
