<?php

namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;
use Cycle\ORM\Promise\Reference;

class User extends Record
{
    public const TABLE = 'users';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
            'role' => Role::class,
            'school' => School::class
        ],
        // Relation::HAS_MANY => [
        //     // 'document' => Document::class,
        // ]
    ];

    /**
     * Set password
     *
     * @param string $plaintextPassword
     * @param bool|null $isTempPassword
     * 
     * @return void
     */
    public function setPassword(string $plaintextPassword, ?bool $isTempPassword = false): void
    {
        if ($isTempPassword) {
            $password = [
                password_hash($plaintextPassword, PASSWORD_BCRYPT),
                $plaintextPassword
            ];
            $this->password = json_encode($password);
        } else {
            $this->password = password_hash($plaintextPassword, PASSWORD_BCRYPT);
        }
    }

    /**
     * Get competencies for a reviewer.
     *
     * @return array
     */
    public function getReviewerCompetencies(): array
    {
        $competencies = $this->getORM()->getRepository(ReviewerCompetency::class)
                              ->select()
                              ->where('user_id', $this->id)
                              ->fetchOne();

        return $competencies->getFields();
    }

    /**
     * Get exams in pending review.
     *
     * @return array
     */
    public function getPendingReviews(): array
    {
        return $this->getORM()->getRepository(ExamReview::class)
                              ->select()
                              ->where(['reviewer_id' => $this->id, 'status' => ExamReview::PENDING_SME_REVIEW])
                              ->fetchAll();
    }

    /**
     * Get fields as array for a reviewer.
     *
     * @return array
     */
    public function getReviewerFields(): array
    {
        $password = json_decode($this->password);

        $reviewer = [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'temp_password' => is_array($password) ? $password[1]: null,
            'school_id' => $this->school_id,
            'competencies' => $this->getReviewerCompetencies(),
            'work_load' => count($this->getPendingReviews())
        ];

        if (get_class($this->school) !== Reference::class) {
            $reviewer['school'] = $this->school->getFields();
        }

        return $reviewer;
    }

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        $user = [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'verified' => $this->verified,
            'suspended' => $this->suspended,
            'school_id' => $this->school_id,
            'can_upload' => $this->can_upload,
            'role_id' => $this->role_id
        ];

        if (get_class($this->school) !== Reference::class) {
            $user['school'] = $this->school->getFields();
        }
        if (get_class($this->role) !== Reference::class) {
            $user['role'] = $this->role->getFields();
        }

        return $user;
    }
}
