<?php

namespace App\Services;

use App\Mail\TwoFactorOtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class TwoFactorService
{
    public const SESSION_USER_ID = 'login.two_factor.id';

    public const SESSION_REMEMBER = 'login.two_factor.remember';

    public function pendingUserId(): ?int
    {
        $userId = Session::get(self::SESSION_USER_ID);

        return is_numeric($userId) ? (int) $userId : null;
    }

    public function pendingUser(): ?User
    {
        $userId = $this->pendingUserId();

        if ($userId === null) {
            return null;
        }

        return User::query()->find($userId);
    }

    public function clearPending(): void
    {
        Session::forget([
            self::SESSION_USER_ID,
            self::SESSION_REMEMBER,
        ]);
    }

    public function send(User $user): OtpCode
    {
        $user->otpCodes()->whereNull('used_at')->delete();

        $plainCode = $this->generateCode();

        $otp = $user->otpCodes()->create([
            'code' => Hash::make($plainCode),
            'expires_at' => now()->addMinutes(config('two_factor.expires_minutes')),
        ]);

        Mail::to($user)->send(new TwoFactorOtpMail(
            user: $user,
            code: $plainCode,
            expiresMinutes: config('two_factor.expires_minutes'),
        ));

        return $otp;
    }

    public function verify(User $user, string $code): bool
    {
        $otp = $user->otpCodes()
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if ($otp === null || ! Hash::check($code, $otp->code)) {
            return false;
        }

        $otp->update(['used_at' => now()]);

        return true;
    }

    public function initiatePendingLogin(User $user, bool $remember): RedirectResponse
    {
        Auth::logout();

        Session::put([
            self::SESSION_USER_ID => $user->id,
            self::SESSION_REMEMBER => $remember,
        ]);

        $this->send($user);

        return redirect()
            ->route('two-factor.show')
            ->with('status', 'Un code de vérification a été envoyé à votre adresse e-mail.');
    }

    public function completePendingLogin(): ?User
    {
        $user = $this->pendingUser();

        if ($user === null) {
            return null;
        }

        Auth::login($user, (bool) Session::get(self::SESSION_REMEMBER, false));
        $this->clearPending();

        return $user;
    }

    private function generateCode(): string
    {
        $length = config('two_factor.code_length');

        return str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
    }
}
