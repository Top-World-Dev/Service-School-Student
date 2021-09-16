<?php
namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;

class PaymentMethod extends Record {
    public const TABLE = 'payment_methods';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
            'user' => User::class,
        ],
    ];

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        $payment_methods = [
            'id' => $this->id,
            'payment_email' => $this->payment_email,
            'type' => $this->type,
            'status' => $this->status,
        ];

        return $payment_methods;
    }
}