<?php

use App\Models\Ride;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id): bool {
    return (int) $user->id === $id;
});

Broadcast::channel('rides.{rideId}', function (User $user, int $rideId): bool {
    $ride = Ride::find($rideId);

    return $ride !== null && $user->can('view', $ride);
});
