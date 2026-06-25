<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Reçu {{ $rideReference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; margin: 0; padding: 24px; }
        .header { background: #111827; color: #fff; padding: 20px 24px; margin: -24px -24px 24px; }
        .brand { font-size: 22px; font-weight: bold; color: #ffce00; }
        .subtitle { color: #d1d5db; margin-top: 4px; font-size: 11px; }
        .badge { display: inline-block; background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 999px; font-size: 10px; font-weight: bold; margin-top: 12px; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        .meta { color: #6b7280; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th { width: 34%; color: #6b7280; font-weight: 600; font-size: 11px; text-transform: uppercase; }
        .total-row td { border-top: 2px solid #111827; font-size: 14px; font-weight: bold; }
        .total-row td:last-child { color: #111827; }
        .footer { margin-top: 28px; padding-top: 14px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 10px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">{{ config('app.name', 'KinTaxiBooking') }}</div>
        <div class="subtitle">Reçu de paiement Mobile Money</div>
        <div class="badge">PAYÉ</div>
    </div>

    <h1>Reçu n° {{ $payment->order_number }}</h1>
    <div class="meta">
        Course {{ $rideReference }} · émis le {{ ($payment->paid_at ?? now())->format('d/m/Y H:i') }}
    </div>

    <table>
        <tr>
            <th>Client</th>
            <td>{{ $payment->user?->firstname }} {{ $payment->user?->lastname }}<br>{{ $payment->user?->email }}</td>
        </tr>
        <tr>
            <th>Trajet</th>
            <td>{{ $payment->ride?->pickup_addr }} → {{ $payment->ride?->dropoff_addr }}</td>
        </tr>
        <tr>
            <th>Chauffeur</th>
            <td>
                @if ($payment->ride?->driver)
                    {{ $payment->ride->driver->firstname }} {{ $payment->ride->driver->lastname }}
                @else
                    —
                @endif
            </td>
        </tr>
        <tr>
            <th>Opérateur</th>
            <td>{{ $methodLabel }}</td>
        </tr>
        <tr>
            <th>Réf. Labyrinthe</th>
            <td>{{ $payment->provider_reference ?: '—' }}</td>
        </tr>
        <tr>
            <th>Montant course</th>
            <td>{{ $netLabel }}</td>
        </tr>
        <tr>
            <th>Frais Labyrinthe</th>
            <td>{{ $feeLabel }}</td>
        </tr>
        <tr class="total-row">
            <td>Total payé</td>
            <td>{{ $amountLabel }}</td>
        </tr>
    </table>

    <div class="footer">
        Ce document atteste du règlement de votre course via la plateforme {{ config('app.name', 'KinTaxiBooking') }}.
        Conservez-le pour vos archives. Pour toute question : contact@kintaxibooking.com
    </div>
</body>
</html>
