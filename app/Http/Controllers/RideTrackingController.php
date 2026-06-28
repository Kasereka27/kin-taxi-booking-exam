<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRideTrackingRequest;
use App\Models\Ride;
use App\Services\RideTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function show(Request $request, Ride $ride, RideTrackingService $tracking): JsonResponse
    {
        $this->authorize('view', $ride);

        if (! $ride->isTrackable() || $ride->driver_id === null) {
            return response()->json(['message' => 'Course non suivie en direct.'], 404);
        }

        return response()->json($tracking->trackingPayload($ride));
    }

    public function updateClient(
        UpdateRideTrackingRequest $request,
        Ride $ride,
        RideTrackingService $tracking,
    ): JsonResponse {
        $this->authorize('trackClient', $ride);

        $ride = $tracking->updateClientPosition(
            $ride,
            (float) $request->validated('lat'),
            (float) $request->validated('lng'),
        );

        return response()->json([
            'status' => $ride->status,
        ]);
    }
}
