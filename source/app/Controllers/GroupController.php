<?php

namespace App\Controllers;

use App\Models\Exam;
use App\Services\GroupService;
use CodeIgniter\HTTP\ResponseInterface;

class GroupController extends BaseController
{

    /**
     * @var GroupService
     */
    protected $groupService;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->groupService = new GroupService();
    }

    /**
     * Get groups that user belongs to.
     *
     * @return mixed
     */
    public function index()
    {
        $groups = $this->groupService->getAll($this->user['id']);
        return $this->getResponse($groups);
    }

    /**
     * Get a group by id
     *
     * @param int $id
     * @return mixed
     */
    public function getById(int $id)
    {
        $group = $this->groupService->getOneById($id);
        $groupData = $group->getFields();
        $exam = Exam::findOne(['group_id' => $groupData['id']], ['professor']);
        $groupUsers = $this->groupService->getGroupUsers($groupData['id'], ['user', 'user.school']);
        $groupData['exam'] = $exam->getFields();
        $groupData['exam']['uploads'] = $exam->getUploads();
        $groupData['users'] = $groupUsers;
        return $this->getResponse(['group' => $groupData]);
    }

    /**
     * Get group users by group id
     * 
     * @param int $id
     * @return mixed
     */
    public function getGroupUsersByGroupId(int $id)
    {
        $groupUsers = $this->groupService->getGroupUsers($id, ['user']);
        return $this->getResponse(['groups' => $groupUsers]);
    }

    /**
     * Get a group's all questions and solutions
     *
     * @param int $id
     * @return mixed
     */
    public function getGroupQA(int $id)
    {
        $qas = $this->groupService->getQAsByGroupId($id, ['group_user']);
        return $this->getResponse(['qas' => $qas]);
    }

    /**
     * Delete a group
     *
     * @param int $id
     * @return mixed
     */
    public function delete(int $id)
    {
        $group = $this->groupService->delete($id);
        return $this->getResponse(['status' => true]);
    }

    /* preview all member's composed QA once every 24hour.
     *
     * @param int $id
     * @return mixed
     */
    public function previewComposedExam(int $id)
    {
        $member = $this->groupService->getGroupUser($id, $this->user['id']);
        $member->last_view_at = new \DateTimeImmutable();
        $member->save();
        return $this->getResponse(['status' => true]);
    }
}