<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRideTrackingRequest;
use App\Models\Ride;
use App\Services\RideTrackingService;
use Illuminate\Http\JsonResponse;

class RideTrackingController extends Controller
{
    public function update(
        UpdateRideTrackingRequest $request,
        Ride $ride,
        RideTrackingService $tracking,
    ): JsonResponse {
        $this->authorize('track', $ride);

        $ride = $tracking->updateDriverPosition(
            $ride,
            (float) $request->validated('lat'),
            (float) $request->validated('lng'),
        );

        return response()->json([
            'status' => $ride->status,
            'eta_minutes' => $tracking->estimateEtaMinutes($ride),
        ]);
    }
}
