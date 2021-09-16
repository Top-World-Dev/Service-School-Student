<?php
namespace App\Models;

use App\CustomAR\Record;

class PasswordReset extends Record {
    public const TABLE = 'password_resets';
    public const RELATIONS = [];

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        return [
            'email' => $this->email,
            'token' => $this->token,
            'created_at' => $this->created_at
        ];
    }
}