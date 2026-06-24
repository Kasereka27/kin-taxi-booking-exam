<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\Ride;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSucceeded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Paiement confirmé')
            ->greeting('Bonjour '.$notifiable->firstname.',')
            ->line('Votre paiement de '.Money::fc($this->payment->amount).' pour la course #'.Ride::referenceFor($this->payment->ride_id).' a bien été confirmé.')
            ->action('Voir la course', route('rides.show', $this->payment->ride_id))
            ->salutation('Merci de votre confiance, l’équipe '.config('app.name'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_succeeded',
            'icon' => '✅',
            'title' => 'Paiement confirmé',
            'message' => 'Paiement de '.Money::fc($this->payment->amount).' confirmé pour la course #'.Ride::referenceFor($this->payment->ride_id).'.',
            'url' => route('rides.show', $this->payment->ride_id),
        ];
    }
}
