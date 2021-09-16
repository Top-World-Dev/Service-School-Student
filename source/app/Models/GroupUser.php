<?php
namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;
use App\Libraries\CloudinaryLibrary;
use Cycle\ORM\Promise\Reference;

class GroupUser extends Record {
    public const TABLE = 'group_users';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
            'group' => Group::class,
            'user'  => User::class,
        ],
    ];

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        $groupUser = [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'user_id' => $this->user_id,
            'grade_value' => $this->grade_value,
            'date_proven' => $this->date_proven ? CloudinaryLibrary::getTempUrl($this->date_proven): null,
            'identity_proven' => $this->identity_proven? CloudinaryLibrary::getTempUrl($this->identity_proven): null,
            'exam_proven' => $this->exam_proven? CloudinaryLibrary::getTempUrl($this->exam_proven): null,
            'verified' => $this->verified,
            'last_view_at' => $this->last_view_at,
        ];

        if ($this->user && (get_class($this->user) !== Reference::class)) {
            $groupUser['user'] = $this->user->getFields();
        }

        return $groupUser;
    }
}