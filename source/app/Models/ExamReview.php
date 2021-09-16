<?php

namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;

class ExamReview extends Record
{
    public const TABLE = 'exam_reviews';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
            'exam' => Exam::class,
            'reviewer' => User::class,
        ]
    ];

    // Define constants for status
    public const PENDING_SME_REVIEW  = 'sme-pending';    // exam sent by admin to Subject Matter Expert (reviewer) for review but not yet reviewed
    public const SME_UNCHANGED       = 'sme-unchanged';  // exam reviewed by Subject Matter Expert (reviewer) and kept without any changes
    public const SME_MODIFIED        = 'sme-modified';   // exam reviewed by Subject Matter Expert (reviewer) and modified
    public const SME_REJECTED        = 'sme-rejected';   // exam reviewed and rejected by Subject Matter Expert (reviewer)

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
            'status' => $this->status,
        ];
    }
}
