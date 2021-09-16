<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\GroupExam;
use App\Models\Exam;

/**
 * Group Service
 */
class GroupService extends BaseService
{
    /**
     * Get groups
     *
     * @param int|null $userId
     * @return array
     */
    public function getAll(int $userId = null): array
    {
        $criteria = [];
        if ($userId) {
            $criteria = ['user_id' => $userId];
        }
        $groupUsers = GroupUser::findAll($criteria);
        $groupIds = array();
        foreach ($groupUsers as $group) {
            array_push($groupIds, $group->group_id);
        }
        if ($groupIds) {
            $groups = Group::findAll(['id' => $groupIds], ['owner']);
            $iterator = array();
            foreach ($groups as $group) {
                $data = $group->getFields();
                $exam = Exam::findOne(['group_id' => $data['id']]);
                $members = GroupUser::findAll(['group_id' => $data['id']]);
                $data['memberCount'] = iterator_count($members);
                $data['exam'] = !empty($exam) ? $exam->getFields() : []; 
                array_push($iterator, $data);
            }
            return $iterator;
        }

        return [];
    }

    /**
     * Save a group
     *
     * @param string $name
     * @param int $userId
     * @return Group
     */
    public function create(string $name, int $userId): Group
    {
        $group = new Group();
        $group->name = $name;
        $group->status = 'initialized';
        $group->owner_id = $userId;
        $group->save();

        return $group;
    }

    /**
     * Get a group by ID
     *
     * @param int $id
     * @return Group
     */
    public function getOneById(int $id): Group
    {
        return Group::getOneByID($id);
    }

    /**
    * Update a group's status
    * runs every 5 mins
    * @param ?int $groupId
    */
    public static function updateGroupStatus(?int $groupId)
    {       
        if ($groupId){
            $groupUsers = self::getGroupUsers($groupId);
             if (count($groupUsers) >= Group::MIN_GROUP_MEMBERS_COUNT) {
                $group = Group::getOneByID($groupId);
                $groupStatus = $group->status;
                if ($groupStatus === Group::STATUS_INIT) {
                    $group->status = Group::STATUS_IN_PROGRESS;
                }
                if ($groupStatus !== Group::STATUS_PENDING_ADMIN_REVIEW && self::checkIfFinalAllQAs($groupId)) {
                     $group->status = Group::STATUS_PENDING_ADMIN_REVIEW;
                }
                $group->save();
            } else {
                $allGroups = Group::findAll([]);
                foreach ($allGroups as $group) {
                    $groupUsers = self::getGroupUsers($group->id);
                    if (count($groupUsers) >= Group::MIN_GROUP_MEMBERS_COUNT) {
                        if ($group->status === Group::STATUS_INIT) {
                            $group->status = Group::STATUS_IN_PROGRESS;
                        }
                        if ($group->status !== Group::STATUS_PENDING_ADMIN_REVIEW && self::checkIfFinalAllQAs($group->id)) {
                            $group->status = Group::STATUS_PENDING_ADMIN_REVIEW;
                        }
                    }
                    $group->save();
                }
            }
        }
    
 


    }

    /**
     * Get a group by name
     *
     * @param string $name
     * @return Group|null
     */
    public function getOneByName(string $name): ?Group
    {
        return Group::findOne(['name' => $name], []);
    }

    /**
     * Find a group&user by group and user
     *
     * @param int $groupId
     * @param int $userId
     * @return GroupUser|null
     */
    public function findOneByGroupAndUser(int $groupId, int $userId): ?GroupUser
    {
        return GroupUser::findOne(['group_id' => $groupId, 'user_id' => $userId], []);
    }

    /**
     * Add a user to a group
     *
     * @param int $groupId
     * @param int $userId
     * @param float|null $gradeValue
     * @return GroupUser
     */
    public function addUser(int $groupId, int $userId, ?float $gradeValue=null): GroupUser
    {
        $groupUser = new GroupUser();
        $groupUser->group_id = $groupId;
        $groupUser->user_id = $userId;
        $groupUser->grade_value = $gradeValue;
        $groupUser->save();
        return $groupUser;
    }

    /**
     * Get users of a group
     *
     * @param int $groupId
     * @param array|null $relation
     * @return array
     */
    public static function getGroupUsers(int $groupId, ?array $relation = []): array
    {
        $users = GroupUser::findAll(['group_id' => $groupId], $relation);
        return self::iteratorToArray($users);
    }

    /**
     * Get a user of a group
     *
     * @param int $groupId
     * @param int $userId
     * @return GroupUser
     */
    public function getGroupUser(int $groupId, int $userId): GroupUser
    {
        return GroupUser::findOne(['group_id' => $groupId, 'user_id' => $userId], []);
    }

    /**
     * Add a question & solution to a group
     *
     * @param int $groupUserId
     * @param array $qa
     */
    public function addQA(int $groupUserId, array $qa)
    {
        $groupExam = new GroupExam();
        $groupExam->group_user_id = $groupUserId;
        $groupExam->question = $qa['question'];
        $groupExam->solution = $qa['solution'];
        $groupExam->is_final = $qa['is_final'];
        $groupExam->save();
    }

    /**
     * Update a question & solution to a group
     *
     * @param int $id
     * @param array $qa
     */
    public function updateQA(int $id, array $qa)
    {
        $groupExam = GroupExam::getOneByID($id);
        $groupExam->question = $qa['question'];
        $groupExam->solution = $qa['solution'];
        $groupExam->is_final = $qa['is_final'];
        $groupExam->save();
    }

    /**
     * Get all QAs of a group
     *
     * @param int $groupId
     * @param array|null $relation
     * @return array
     */
    public function getQAsByGroupId(int $groupId, ?array $relation = []): array
    {
        $qas = GroupExam::findAll(['group_user.group_id' => $groupId], $relation);
        return $this->iteratorToArray($qas);
    }

    /**
     * Get all QAs of a member
     *
     * @param int $groupUserId
     * @return array
     */
    public function getMemberQA(int $groupUserId): array
    {
        $qas = GroupExam::findAll(['group_user_id' => $groupUserId], []);
        return $this->iteratorToArray($qas);
    }

    /**
     * Check if a user can join to new group
     *
     * @param int $userId
     * @return bool
     */
    public function checkCreateOrJoinAvailability(int $userId): bool
    {
        $groupUsers = GroupUser::findAll(['group.status' => ['initialized', 'progress'], 'user_id' => $userId], []);
        if (iterator_count($groupUsers) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Delete a group
     *
     * @param int $groupId
     */
    public static function delete(int $groupId)
    {
        // Delete members and QAs
        $groupUsers = GroupUser::findAll(['group_id' => $groupId]);
        foreach ($groupUsers as $user) {
            $qas = GroupExam::findAll(['group_user_id' => $user->id]);
            foreach ($qas as $qa) {
                $qa->delete();
            }
            $user->delete();
        }

        // Delete exam
        $exam = Exam::findOne(['group_id' => $groupId]);
        $exam->delete();

        // Delete group
        $group = Group::getOneByID($groupId);
        $group->delete();
    }

    /**
     * Delete a group member
     *
     * @param int $memberId
     */
    public function deleteMember(int $memberId)
    {
        $member = GroupUser::getOneByID($memberId);
        $qas = GroupExam::findAll(['group_user_id' => $memberId]);
        foreach ($qas as $qa) {
            $qa->delete();
        }
        $user->delete();
    }

    /**
     * Check if member is owner of a group
     *
     * @param int $groupId
     * @param int $userId
     * @return bool
     */
    public function isGroupOwner(int $groupId, int $userId): bool
    {
        $group = Group::findOne(['group_id' => $groupId, 'owner_id' => $userId], []);
        if (!empty($group)) {
            return true;
        }
        return false;
    }
    
    /**
     * Check if all members have made their final QAs submission
     *
     * @param int $groupId
     * @return bool
     */
    public static function checkIfFinalAllQAs(int $groupId): bool
    {
        $groupUsers = GroupUser::findAll(['group_id' => $groupId], []);
        foreach ($groupUsers as $user) {
            $qas = GroupExam::findAll(['group_user_id' => $user->id]);
            $final_qas = GroupExam::findAll(['is_final' => true]);
            if (empty($qas) || iterator_count($qas) !== iterator_count($final_qas)) {
                return false;
            }
        }
        return true;
    }
}

