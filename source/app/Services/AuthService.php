<?php declare(strict_types=1);

namespace App\Services;

use App\Models\PasswordReset;
use App\Models\User;
use Ramsey\Uuid\Uuid;

/**
 * User Service
 */
class AuthService
{
    /**
     * Create password reset token
     *
     * @param string $email
     */
    public function createPasswordResetToken(string $email)
    {
        $token = Uuid::uuid4();

        $password_reset = PasswordReset::findOne(['email' => $email], []);
        if (empty($password_reset)) {
            $password_reset = new PasswordReset();
            $password_reset->email = $email;
            $password_reset->token = $token;
            $password_reset->save();
        } else {
            $password_reset->token = $token;
            $password_reset->save();
        }

        return $token;
    }

    /**
     * Check password reset token
     *
     * @param string $token
     */
    public function checkPasswordResetToken(string $token)
    {
        $password_reset = PasswordReset::findOne(['token' => $token], []);
        if (empty($password_reset)) {
            return [
                'success' => false,
                'message' => 'Token is invalid'
            ];
        } else {
            $expire_time = $password_reset->updated_at->add(new \DateInterval('PT1H'));
            $now = new \DateTimeImmutable();
            if ($expire_time < $now ) {
                return [
                    'success' => false,
                    'message' => 'Token is expired'
                ];
            }
            return [
                'success' => true,
            ];
        }
    }

    /**
     * Reset password
     *
     * @param string $token
     * @param string $password
     */
    public function resetPassword(string $token, string $password)
    {
        $password_reset = PasswordReset::findOne(['token' => $token], []);
        if (empty($password_reset)) {
            return [
                'success' => false,
                'message' => 'Token is invalid'
            ];
        } else {
            $expire_time = $password_reset->updated_at->add(new \DateInterval('PT1H'));
            $now = new \DateTimeImmutable();
            if ($expire_time < $now ) {
                return [
                    'success' => false,
                    'message' => 'Token is expired'
                ];
            }

            $user = User::findOne(['email' => $password_reset->email], []);
            if ($user) {
                $user->setPassword($password);
                $user->save();
                $password_reset->delete();
                return [
                    'success' => true,
                ];
            }
            return [
                'success' => false,
                'message' => 'Not found email'
            ];
        }
    }
}