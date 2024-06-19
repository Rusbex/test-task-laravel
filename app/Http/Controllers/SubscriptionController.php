<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->input('email');
        $encryptedEmail = Crypt::encryptString($email);
        $expiresAt = Carbon::now()->addHour()->timestamp;
        $token = base64_encode(json_encode([
            'email' => $encryptedEmail,
            'expires_at' => $expiresAt,
        ]));

        $verificationUrl = route('verify', ['token' => $token]);

        // Отправляем email пользователю
        Mail::send([], [], function ($message) use ($email, $verificationUrl) {
            $message->to($email)
                ->subject('Подтверждение подписки')
                ->html("Пожалуйста, подтвердите свою подписку, перейдя по следующей ссылке: <a href=\"$verificationUrl\">$verificationUrl</a>");
        });

        return response()->json(['message' => 'Подтверждение отправлено на ваш email.']);
    }

    public function verify(Request $request)
    {
        $token = $request->query('token');

        try {
            $data = json_decode(base64_decode($token), true);
            if (!$data || !isset($data['email']) || !isset($data['expires_at'])) {
                return response()->json(['message' => 'Неверная ссылка подтверждения.'], 400);
            }

            if (Carbon::now()->timestamp > $data['expires_at']) {
                return response()->json(['message' => 'Ссылка подтверждения истекла.'], 400);
            }

            $email = Crypt::decryptString($data['email']);

            // Здесь вы можете выполнить любые действия с подтвержденным email
            return response()->json(['message' => 'Email успешно подтвержден.', 'email' => $email]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Неверная или истекшая ссылка подтверждения.'], 400);
        }
    }
}
