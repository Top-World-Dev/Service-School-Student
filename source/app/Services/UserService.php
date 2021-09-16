<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Admin;
use App\Models\Student;
use App\Models\User;
use App\Models\Role;
use App\Models\PaymentMethod;

/**
 * User Service
 */
class UserService
{
    /**
     * Save a role data
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $password
     * @param string $role
     * @param int $schoolId
     * @param string|null $email_verification_code
     * @param bool|null $is_reviewer
     */
    public function createUser(string $firstName, string $lastName, string $email, string $password, string $role, int $schoolId, ?string $email_verification_code = null, ?bool $is_reviewer = null)
    {
        switch ($role) {
            case Role::ADMIN :
                $user = new Admin();
                break;

            case Role::STUDENT :
                $user = new Student();
                break;

            case Role::REVIEWER :
                $user = new User();
                $user->role_id = Role::REVIEWER;
                break;

            default:
                break;
        }

        $user->email = $email;
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->setPassword($password, $is_reviewer);
        $user->school_id = $schoolId;
        $user->email_verification_code = $email_verification_code;
        $user->save();

        return $user;
    }

    /**
     * Get a user by ID
     *
     * @param int $id
     * @param array|null $relations
     * @return User|null
     */
    public function getUserById(int $id, ?array $relations=[]): ?User
    {
        return User::getOneByID($id, $relations);
    }

    /**
     * Get all students
     *
     * @return array
     */
    public function getStudents(): array
    {
        $relations = ['school'];
        $students = User::findAll(['role.name' => 'Student'], $relations);

        $iterator = array();
        foreach ($students as $student) {
            array_push($iterator, $student->getFields());
        }
        return $iterator;
    }

    /**
     * Get a user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findUserByEmailAddress(string $email): ?User
    {
        return User::findOne(['email' => $email], ['role']);
    }

    /**
     * Suspend a user's account
     *
     * @param int $id
     */
    public function suspend(int $id)
    {
        $user = User::getOneByID($id, []);
        $user->suspended = true;
        $user->save();
    }

    /**
     *  Restore a suspended account
     *
     * @param int $id
     */
    public function restore(int $id)
    {
        $user = User::getOneByID($id, []);
        $user->suspended = false;
        $user->save();
    }

    /**
     * Get user's payment methods.
     *
     * @param int $user_id
     * @return array
     */
    public function getPaymentMethods(int $user_id): array
    {
        $payment_methods = PaymentMethod::findAll(['user_id' => $user_id], []);

        $iterator = array();
        foreach ($payment_methods as $payment_method) {
            array_push($iterator, $payment_method->getFields());
        }
        return $iterator;
    }

    /**
     * Verify user's email
     *
     * @param string $code
     * @return User|null
     */
    public function verifyEmail(string $code): ?User
    {
        $user = User::findOne(['email_verification_code' => $code], []);
        if ($user) {
            $user->email_verification_code = null;
            $user->verified = true;
            $user->can_upload = true;
            $user->save();
        }
        return $user;
    }

    /**
     * Update a user
     *
     * @param int $id
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $password
     */
    public function update(int $id, string $firstName, string $lastName, string $email, string $password)
    {
        $user = User::getOneByID($id, []);
        $user->email = $email;
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        if ($password) {
            $user->setPassword($password);
        }
        $user->save();
    }
}
