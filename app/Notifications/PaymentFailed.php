<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\Ride;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailed extends Notification implements ShouldQueue
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
            ->subject('Paiement refusé')
            ->greeting('Bonjour '.$notifiable->firstname.',')
            ->line('Votre paiement de '.Money::fc($this->payment->amount).' pour la course #'.Ride::referenceFor($this->payment->ride_id).' n’a pas abouti.')
            ->line($this->reasonText())
            ->action('Réessayer le paiement', route('rides.pay', $this->payment->ride_id))
            ->salutation('L’équipe '.config('app.name'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_failed',
            'icon' => '❌',
            'title' => 'Paiement refusé',
            'message' => 'Le paiement de la course #'.Ride::referenceFor($this->payment->ride_id).' a été refusé. '.$this->reasonText(),
            'url' => route('rides.pay', $this->payment->ride_id),
        ];
    }

    private function reasonText(): string
    {
        return match ($this->payment->failure_reason) {
            'expired' => 'Le code PIN de confirmation n’a pas été saisi à temps sur votre téléphone.',
            'declined' => 'Le paiement a été refusé par votre opérateur Mobile Money.',
            default => 'Vous pouvez réessayer.',
        };
    }
}
