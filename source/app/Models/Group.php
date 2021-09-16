<?php

namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;
use Cycle\ORM\Promise\Reference;

class Group extends Record
{
    public const TABLE = 'groups';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
            'owner'  => User::class,
        ],
    ];

    /**
     * @var int
     */
    public const MIN_GROUP_MEMBERS_COUNT = 3;

    /**
     * @var int
     */
    private const MAX_GROUP_MEMBERS_COUNT = 5;

    // Define constants for status
    public const STATUS_INIT = 'initialized';  // group created but has less than 3 members
    public const STATUS_IN_PROGRESS = 'in-progress';  // group created and has at least 3 members
    public const STATUS_PENDING_ADMIN_REVIEW = 'pending-admin-review';  // all group members have submitted their final Q&As
    public const STATUS_ACTIVE = 'active';  // all group members have uploaded their actual returned graded exam and admins have verified and approved their scores (i.e. at least 1 of the members in the group scored a 92% or above OR at least 2 of the members in the group scored 85% or above OR at least 3 of the members in the group scored 80% or above.)
    public const STATUS_SYSTEM_DISMISSED = 'system-dismissed';  // group marked for deletion by the system/cronjob

    // Define constants for quality
    public const QUALITY_POOR = 'poor';
    public const QUALITY_UNEVEN = 'uneven';
    public const QUALITY_GOOD = 'good';

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        $group = [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'owner_id' => $this->owner_id,
            'rejected_at' => $this->rejected_at,
            'deleted_at' => $this->deleted_at
        ];

        if (get_class($this->owner) !== Reference::class) {
            $group['owner'] = $this->owner->getFields();
        }

        return $group;
    }
}
