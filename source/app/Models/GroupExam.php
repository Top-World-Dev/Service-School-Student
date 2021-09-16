<?php
namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;
use Cycle\ORM\Promise\Reference;

class GroupExam extends Record {
    public const TABLE = 'group_exams';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
            'group_user' => GroupUser::class,
        ],
    ];

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        $groupExam = [
            'id' => $this->id,
            'group_user_id' => $this->group_user_id,
            'question' => $this->question,
            'solution' => $this->solution,
            'is_final' => $this->is_final
        ];

        if (get_class($this->group_user) !== Reference::class) {
            $groupExam['group_user'] = $this->group_user->getFields();
        }

        return $groupExam;
    }
}