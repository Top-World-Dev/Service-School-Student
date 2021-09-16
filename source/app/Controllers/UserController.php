<?php

namespace App\Controllers;

use App\Models\Group;
use App\Models\GroupExam;
use App\Models\GroupUser;
use App\Services\UserService;
use App\Services\PaymentMethodService;
use CodeIgniter\HTTP\ResponseInterface;

class UserController extends BaseController
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var PaymentMethodService
     */
    protected $paymentMethodService;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->userService = new UserService();
        $this->paymentMethodService = new PaymentMethodService();
    }

    /**
     * Get all students.
     *
     * @return mixed
     */
    public function getStudents()
    {
        $students = $this->userService->getStudents();
        return $this->getResponse($students);
    }

    /**
     * Get a user by Id.
     *
     * @param int $id
     * @return mixed
     */
    public function getById(int $id)
    {
        $user = $this->userService->getUserById($id, ['school']);
        $userData = $user->getFields();
        return $this->getResponse(['user' => $userData]);
    }

    /**
     * Get all students.
     *
     * @return mixed
     */
    public function suspendUser()
    {
        $input = $this->getRequestInput($this->request);
        $user = $this->userService->suspend($input['id']);

        $members = GroupUser::findAll(['user_id' => $input['id']], []);
        $groupIds = [];
        $memberIds = [];

        if (!empty($members)) {
            // Delete member from all groups
            foreach ($members as $member) {
                array_push($groupIds, $member->group_id);
                array_push($memberIds, $member->id);
                $member->delete();
            }

            // Delete QAs
            foreach ($memberIds as $memberId) {
                $qas = GroupExam::findAll(['group_user_id' => $memberId]);
                foreach ($qas as $qa) {
                    $qa->delete();
                }
            }

            // Delete a group if user is owner of the group.
            $groups = Group::findAll(['owner_id' => $input['id']], []);
            foreach ($groups as $group) {
                $group->delete();
            }
        }

        return $this->getResponse(['status' => true]);
    }

    /**
     * Get all students.
     *
     * @return mixed
     */
    public function restoreUser()
    {
        $input = $this->getRequestInput($this->request);
        $user = $this->userService->restore($input['id']);
        return $this->getResponse(['status' => true]);
    }

    /**
     * Get all students.
     *
     * @return mixed
     */
    public function getPaymentMethods()
    {
        $payment_methods = $this->userService->getPaymentMethods($this->user['id']);
        return $this->getResponse($payment_methods);
    }

    /**
     * Save payment method
     *
     * @return mixed
     */
    public function savePaymentMethod()
    {
        $input = $this->getRequestInput($this->request);
        $payment_method = $this->paymentMethodService->create($this->user['id'], $input['email'], $input['type']);
        return $this->getResponse(['status' => true]);
    }

    /**
     * Remove payment method
     *
     * @param int $id
     * @return mixed
     */
    public function removePaymentMethod(int $id)
    {
        $payment_method = $this->paymentMethodService->remove($id);
        return $this->getResponse(['status' => true]);
    }

    /**
     * Verify user's email.
     *
     * @param string $code
     * @return mixed
     */
    public function verifyEmail(string $code)
    {
        $user = $this->userService->verifyEmail($code);
        if($user) {
            return $this->getResponse(['success' => true]);
        }
        return $this->getResponse(['success' => false]);
    }

    /**
     * Update authenticated user's info
     *
     * @return mixed
     */
    public function updateMe()
    {
        $input = $this->getRequestInput($this->request);
        $authUserId = $this->user['id'];
        $user = $this->userService->update($authUserId, $input['first_name'], $input['last_name'], $input['email'], $input['password']);
        return $this->getResponse(['status' => true]);
    }
}
