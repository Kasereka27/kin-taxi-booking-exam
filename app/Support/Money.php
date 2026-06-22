<?php

namespace App\Support;

class Money
{
    /**
     * Formate un montant en francs congolais, ex. « 35 000 FC ».
     */
    public static function fc(int|float|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }

        return number_format((float) $amount, 0, ',', ' ').' FC';
    }
}
