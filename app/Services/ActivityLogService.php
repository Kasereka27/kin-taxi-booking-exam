<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogService
{
    public const ACTION_LOGIN = 'login';

    public const ACTION_LOGOUT = 'logout';

    public const ACTION_RIDE_CREATED = 'ride.created';

    public const ACTION_RIDE_ACCEPTED = 'ride.accepted';

    public const ACTION_RIDE_CANCELLED = 'ride.cancelled';

    public const ACTION_USER_BLOCKED = 'user.blocked';

    public const ACTION_USER_REACTIVATED = 'user.reactivated';

    public const ACTION_PAYMENT_SUCCESS = 'payment.success';

    public const ACTION_PAYMENT_FAILED = 'payment.failed';

    /**
     * @return array<string, string>
     */
    public static function actionLabels(): array
    {
        return [
            self::ACTION_LOGIN => 'Connexion',
            self::ACTION_LOGOUT => 'Déconnexion',
            self::ACTION_RIDE_CREATED => 'Course créée',
            self::ACTION_RIDE_ACCEPTED => 'Course acceptée',
            self::ACTION_RIDE_CANCELLED => 'Course annulée',
            self::ACTION_USER_BLOCKED => 'Compte bloqué',
            self::ACTION_USER_REACTIVATED => 'Compte réactivé',
            self::ACTION_PAYMENT_SUCCESS => 'Paiement réussi',
            self::ACTION_PAYMENT_FAILED => 'Paiement échoué',
        ];
    }

    public function log(
        string $action,
        ?string $description = null,
        ?User $user = null,
        ?Request $request = null,
    ): ActivityLog {
        $request ??= request();

        return ActivityLog::query()->create([
            'user_id' => $user?->id ?? $request?->user()?->id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
