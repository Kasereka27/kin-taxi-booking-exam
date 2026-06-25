<?php

namespace App\Events;

use App\Models\Ride;
use App\Services\RideTrackingService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideTrackingUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Ride $ride) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('rides.'.$this->ride->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'tracking.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->ride->loadMissing(['driver.driverProfile']);
        $coords = $this->ride->driverCoordinates();
        $tracking = app(RideTrackingService::class);

        return [
            'ride_id' => $this->ride->id,
            'status' => $this->ride->status,
            'lat' => $coords[0] ?? null,
            'lng' => $coords[1] ?? null,
            'eta_minutes' => $tracking->estimateEtaMinutes($this->ride),
        ];
    }
}
