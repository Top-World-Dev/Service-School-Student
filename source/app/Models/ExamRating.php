<?php

namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;

class ExamRating extends Record
{
    public const TABLE = 'exam_ratings';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
            'exam' => Exam::class,
            'user' => User::class,
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
            'user_id' => $this->user_id,
            'stars' => $this->stars,
            'review_body' => $this->review_body
        ];
    }
}
