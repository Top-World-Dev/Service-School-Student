<?php

namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;

class ExamContent extends Record
{
    public const TABLE = 'exam_contents';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
            'exam' => Exam::class,
            'reviewer' => User::class,
        ]
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
            'exam_id' => $this->exam_id,
            'reviewer_id' => $this->reviewer_id,
            'question' => $this->question,
            'solution' => $this->solution,
        ];
    }
}
