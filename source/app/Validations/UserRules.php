<?php

namespace App\Validations;

use App\Services\UserService;
use Exception;

class UserRules
{
    public function validateUser(string $str, string $fields, array $data): bool
    {
        try {
            $userService = new UserService();
            $user = $userService->findUserByEmailAddress($data['email']);
            return password_verify($data['password'], $user->password);
        } catch (Exception $e) {
            return false;
        }
    }
}