<?php
namespace App\Models;

use App\CustomAR\Role as RoleDefinition;

class Student extends User {
    protected static $role = RoleDefinition::STUDENT;

    public function __construct() {
        $this->role_id = Role::STUDENT;
    }

    /**
     * Get payment methods
     *
     * @return array
     */
    public function getPaymentMethods(): array
    {
        $select = $this->getORM()->getRepository(PaymentMethod::class)
                              ->select()
                              ->where('user_id', $this->id);

        $data = $select->fetchData();
        $iterator = self::getIterator(PaymentMethod::class, $data);
        $payment_methods = array();
        foreach ($iterator as $iterator) {
            array_push($payment_methods, $iterator->getFields());
        }
        return $payment_methods;
    }

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'verified' => $this->verified,
        ];
    }
}