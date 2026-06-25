<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewReceipt(User $user, Payment $payment): bool
    {
        return $payment->isSuccessful() && $payment->user_id === $user->id;
    }
}
