<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Ride;
use App\Support\Money;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PaymentReceiptService
{
    public function generate(Payment $payment): string
    {
        if ($payment->receipt_path && Storage::disk('public')->exists($payment->receipt_path)) {
            return $payment->receipt_path;
        }

        $payment->loadMissing(['ride.client', 'ride.driver', 'user']);

        $path = 'receipts/recu-'.$payment->order_number.'.pdf';

        $pdf = Pdf::loadView('pdf.payment-receipt', [
            'payment' => $payment,
            'rideReference' => Ride::referenceFor($payment->ride_id),
            'amountLabel' => Money::fc($payment->amount),
            'feeLabel' => Money::fc($payment->fee),
            'netLabel' => Money::fc($payment->netAmount()),
            'methodLabel' => strtoupper($payment->method),
        ])->setPaper('a4');

        Storage::disk('public')->put($path, $pdf->output());

        $payment->update(['receipt_path' => $path]);

        return $path;
    }

    public function download(Payment $payment): Response
    {
        $path = $this->generate($payment);

        return Storage::disk('public')->download(
            $path,
            'recu-'.Ride::referenceFor($payment->ride_id).'.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }
}
