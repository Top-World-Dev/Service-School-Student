<?php

namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;

class ReviewerCompetency extends Record
{
    public const TABLE = 'reviewer_competencies';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
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
            'user_id' => $this->user_id,
            'disciplines' => json_decode($this->disciplines),
            'levels' => json_decode($this->levels),
            'subjects' => json_decode($this->subjects),
        ];
    }
}
