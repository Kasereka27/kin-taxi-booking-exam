<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\LabyrinthePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LabyrintheCallbackController extends Controller
{
    public function __construct(private readonly LabyrinthePaymentService $paymentService) {}

    /**
     * Webhook appelé par Labyrinthe après validation (ou échec) du paiement.
     * Doit toujours répondre 200 pour confirmer la réception.
     */
    public function handle(Request $request): Response
    {
        $payload = $request->all();

        Log::info('Labyrinthe callback reçu', $payload);

        $reference = $payload['reference']
            ?? $payload['orderNumber']
            ?? ($payload['results']['details']['reference'] ?? null);

        $statusCode = $payload['results']['status']['code']
            ?? ($payload['status']['code'] ?? null);

        if ($reference === null || $statusCode === null) {
            return response('Bad Request', 400);
        }

        $payment = Payment::where('order_number', $reference)
            ->orWhere('provider_reference', $reference)
            ->first();

        if ($payment === null) {
            Log::warning('Paiement introuvable pour le callback Labyrinthe', ['reference' => $reference]);

            return response('Not Found', 404);
        }

        $this->paymentService->applyStatus($payment, (int) $statusCode, $payload);

        return response('OK', 200);
    }
}
