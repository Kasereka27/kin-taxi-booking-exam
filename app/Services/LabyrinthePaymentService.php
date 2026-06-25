<?php

namespace App\Services;

use App\Mail\PaymentConfirmedMail;
use App\Models\Payment;
use App\Models\Ride;
use App\Models\User;
use App\Notifications\PaymentFailed;
use App\Notifications\PaymentSucceeded;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class LabyrinthePaymentService
{
    /**
     * Pourcentage de commission prélevé par Labyrinthe et répercuté sur le client.
     */
    public static function commissionPercent(): float
    {
        return (float) config('labyrinthe.commission_percent', 0);
    }

    /**
     * Montant de la commission (arrondi à l'unité) pour un prix de course donné.
     */
    public static function commissionFor(float $basePrice): float
    {
        return round($basePrice * self::commissionPercent() / 100);
    }

    /**
     * Total à payer par le client : prix de la course + commission Labyrinthe.
     */
    public static function totalWithCommission(float $basePrice): float
    {
        return $basePrice + self::commissionFor($basePrice);
    }

    /**
     * Détail tarifaire affiché au client avant paiement.
     *
     * @return array{base: float, fee: float, total: float, percent: float}
     */
    public static function priceBreakdown(float $basePrice): array
    {
        return [
            'base' => $basePrice,
            'fee' => self::commissionFor($basePrice),
            'total' => self::totalWithCommission($basePrice),
            'percent' => self::commissionPercent(),
        ];
    }

    /**
     * Initie un dépôt Mobile Money pour régler une course.
     *
     * Crée d'abord un paiement « pending » en base (anti-perte), puis appelle
     * l'API Labyrinthe. Le statut final arrivera via le webhook de callback.
     *
     * @return array{success: bool, message: string, payment: Payment}
     */
    public function initiateDeposit(Ride $ride, User $payer, string $phone, string $method): array
    {
        $orderNumber = 'DEP-'.Str::uuid()->toString();
        $basePrice = (float) $ride->price;
        $fee = self::commissionFor($basePrice);
        $amount = $basePrice + $fee;
        $currency = (string) config('labyrinthe.currency', 'CDF');

        $payment = $ride->payments()->create([
            'user_id' => $payer->id,
            'order_number' => $orderNumber,
            'method' => $method,
            'amount' => $amount,
            'fee' => $fee,
            'currency' => $currency,
            'status' => 'pending',
        ]);

        $payload = [
            'token' => config('labyrinthe.token'),
            'phone' => $phone,
            'amount' => $amount,
            'currency' => $currency,
            'country' => config('labyrinthe.country', 'CD'),
            'callback' => config('labyrinthe.callback_url'),
            'reference' => $orderNumber,
            'gateway' => config('labyrinthe.gateway'),
        ];

        try {
            $response = $this->client()
                ->post($this->endpoint(config('labyrinthe.deposit_endpoint', '/payment/mobile')), $payload);

            $body = $response->json() ?? [];

            if ($response->successful() && ($body['success'] ?? false)) {
                $payment->update([
                    'provider_reference' => $body['orderNumber'] ?? $body['reference'] ?? null,
                    'method' => $this->normalizeProvider($body['results']['details']['provider']['name'] ?? null, $method),
                ]);

                return [
                    'success' => true,
                    'message' => $body['message'] ?? 'Paiement initié. Veuillez valider sur votre téléphone.',
                    'payment' => $payment->refresh(),
                ];
            }

            Log::error('Labyrinthe deposit failed', ['order' => $orderNumber, 'response' => $body]);
            $payment->update(['status' => 'failed']);

            return [
                'success' => false,
                'message' => $body['message'] ?? "Échec de l'initiation du paiement.",
                'payment' => $payment->refresh(),
            ];
        } catch (Throwable $e) {
            Log::error('Labyrinthe deposit exception', ['order' => $orderNumber, 'error' => $e->getMessage()]);
            $payment->update(['status' => 'failed']);

            return [
                'success' => false,
                'message' => 'Service de paiement indisponible. Réessayez plus tard.',
                'payment' => $payment->refresh(),
            ];
        }
    }

    /**
     * Vérifie le statut d'un paiement auprès de Labyrinthe et met la base à jour.
     *
     * @return array{success: bool, status: string, message: string}
     */
    public function checkTransaction(Payment $payment): array
    {
        $reference = $payment->provider_reference ?: $payment->order_number;
        $success = false;
        $message = '';

        try {
            $response = $this->client()
                ->get(rtrim((string) config('labyrinthe.gateway'), '/').'/check/'.$reference, [
                    'token' => config('labyrinthe.token'),
                ]);

            $body = $response->json() ?? [];
            $code = $body['results']['status']['code'] ?? null;

            if ($code !== null) {
                $this->applyStatus($payment, (int) $code, $body);
            }

            $success = (bool) ($body['success'] ?? false);
            $message = $body['message'] ?? '';
        } catch (Throwable $e) {
            Log::error('Labyrinthe check exception', ['payment' => $payment->id, 'error' => $e->getMessage()]);
            $message = 'Vérification impossible pour le moment.';
        }

        // Le paiement est toujours en attente : on le marque refusé s'il a expiré
        // (code PIN non saisi à temps), pour ne pas laisser le suivi tourner sans fin.
        if ($payment->refresh()->isPending()) {
            $this->expireIfStale($payment);
        }

        return [
            'success' => $success,
            'status' => $payment->refresh()->status,
            'message' => $message,
        ];
    }

    /**
     * Marque un paiement « pending » comme refusé lorsqu'il dépasse le délai de
     * confirmation (aucun code PIN saisi sur le téléphone du client).
     */
    public function expireIfStale(Payment $payment): bool
    {
        if (! $payment->isPending()) {
            return false;
        }

        $timeout = (int) config('labyrinthe.payment_timeout', 120);

        if ($payment->created_at === null || $payment->created_at->diffInSeconds(now()) < $timeout) {
            return false;
        }

        $payment->update([
            'status' => 'failed',
            'failure_reason' => 'expired',
        ]);

        $this->notifyPaymentOutcome($payment);

        return true;
    }

    /**
     * Applique un code de statut Labyrinthe (0 Pending, 1 Failed, 2 Success) au paiement.
     *
     * @param  array<string, mixed>  $payload
     */
    public function applyStatus(Payment $payment, int $code, array $payload = []): void
    {
        $previous = $payment->status;

        $status = match ($code) {
            2 => 'success',
            1 => 'failed',
            default => 'pending',
        };

        $payment->update([
            'status' => $status,
            'failure_reason' => $status === 'failed' ? 'declined' : null,
            'callback_payload' => json_encode($payload),
            'paid_at' => $status === 'success' ? now() : $payment->paid_at,
        ]);

        // On notifie une seule fois, au moment où le paiement quitte l'état « pending ».
        if ($previous === 'pending' && in_array($status, ['success', 'failed'], true)) {
            $this->notifyPaymentOutcome($payment);
        }
    }

    /**
     * Avertit le client de l'issue de son paiement (réussi ou refusé).
     */
    private function notifyPaymentOutcome(Payment $payment): void
    {
        $client = $payment->user;

        if ($client === null) {
            return;
        }

        if ($payment->status === 'success') {
            app(PaymentReceiptService::class)->generate($payment->fresh());
            $payment->refresh();

            $client->notify(new PaymentSucceeded($payment));
            Mail::to($client)->queue(new PaymentConfirmedMail($payment));
        } elseif ($payment->status === 'failed') {
            $client->notify(new PaymentFailed($payment));
        }
    }

    private function client(): PendingRequest
    {
        $client = Http::acceptJson()->timeout((int) config('labyrinthe.timeout', 30));

        if (! config('labyrinthe.verify_ssl', true)) {
            $client->withoutVerifying();
        }

        return $client;
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('labyrinthe.base_url'), '/').'/'.ltrim($path, '/');
    }

    private function normalizeProvider(?string $providerName, string $fallback): string
    {
        $name = strtolower((string) $providerName);

        return match (true) {
            str_contains($name, 'pesa') => 'mpesa',
            str_contains($name, 'airtel') => 'airtel',
            str_contains($name, 'orange') => 'orange',
            default => $fallback,
        };
    }
}
