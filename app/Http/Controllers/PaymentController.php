<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Ride;
use App\Services\LabyrinthePaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly LabyrinthePaymentService $paymentService) {}

    /**
     * Formulaire de paiement Mobile Money d'une course terminée.
     */
    public function create(Ride $ride): View|RedirectResponse
    {
        $this->authorize('pay', $ride);

        return view('pageContent.payment', [
            'ride' => $ride,
            'breakdown' => LabyrinthePaymentService::priceBreakdown((float) $ride->price),
        ]);
    }

    /**
     * Initie le paiement de la course via Labyrinthe.
     */
    public function store(Request $request, Ride $ride): RedirectResponse
    {
        $this->authorize('pay', $ride);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^0[89]\d{8}$/'],
            'method' => ['required', 'in:mpesa,airtel,orange'],
        ], [
            'phone.regex' => 'Le numéro doit être un numéro Mobile Money valide (ex. 0891234567).',
            'method.in' => 'Choisissez un opérateur valide.',
        ]);

        $minAmount = (int) config('labyrinthe.min_amount', 500);
        if (LabyrinthePaymentService::totalWithCommission((float) $ride->price) < $minAmount) {
            return back()->with('error', "Le montant minimum de paiement est de {$minAmount} CDF.");
        }

        $result = $this->paymentService->initiateDeposit(
            ride: $ride,
            payer: $request->user(),
            phone: $validated['phone'],
            method: $validated['method'],
        );

        if ($result['success']) {
            return redirect()
                ->route('payments.status', $result['payment'])
                ->with('status', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Page de suivi du statut d'un paiement (vérifie auprès de Labyrinthe).
     */
    public function status(Request $request, Payment $payment): View
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        if ($payment->isPending()) {
            $this->paymentService->checkTransaction($payment);
            $payment->refresh();
        }

        $payment->load('ride');

        return view('pageContent.paymentStatus', ['payment' => $payment]);
    }

    /**
     * Endpoint léger interrogé en AJAX pour le suivi automatique du statut.
     * Vérifie activement auprès de Labyrinthe tant que le paiement est en attente.
     */
    public function poll(Request $request, Payment $payment): JsonResponse
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        if ($payment->isPending()) {
            $this->paymentService->checkTransaction($payment);
            $payment->refresh();
        }

        return response()->json(['status' => $payment->status]);
    }
}
