<?php

namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;
use Cycle\ORM\Promise\Reference;

class ExamMatch extends Record
{
    public const TABLE = 'matches';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
            'request' => Request::class,
            'exam' => Exam::class,
        ],
    ];

    // Define constants for status
    public const PENDING  = 'pending';
    public const SELECTED = 'selected';
    public const PAID     = 'paid';

    /**
     * Get payments
     *
     * @return array
     */
    public function getPayments(): array
    {
        $data = $this->getORM()->getRepository(Payment::class)
            ->select()
            ->where('match_id', $this->id)
            ->fetchData();

        $iterator = self::getIterator(Payment::class, $data);
        $payments = array();
        foreach ($iterator as $iterator) {
            array_push($payments, $iterator->getFields());
        }
        return $payments;
    }

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        $match = [
            'id' => $this->id,
            'request_id' => $this->request_id,
            'exam_id' => $this->exam_id,
            'status' => $this->status,
            'paid' => $this->paid,
            'updated_at' => $this->updated_at
        ];

        if (get_class($this->exam) !== Reference::class) {
            $match['exam'] = $this->exam->getFields();
        }
        if (get_class($this->request) !== Reference::class) {
            $match['request'] = $this->request->getFields();
        }

        return $match;
    }
}
