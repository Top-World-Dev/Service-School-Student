<?php
namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;

class Payment extends Record {
    public const TABLE = 'payments';
    public const RELATIONS = [
         Relation::BELONGS_TO => [
            'match' => ExamMatch::class,
        ],
    ];

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'payment_option' => $this->payment_option,
            'pp_batch_id' => $this->pp_batch_id,
            'pp_item_id' => $this->pp_item_id,
            'amount' => $this->amount,
            'transfer_id' => $this->transfer_id,
            'status' => $this->status,
            'type' => $this->type,
        ];
    }
}