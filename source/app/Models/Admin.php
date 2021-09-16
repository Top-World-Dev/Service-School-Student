<?php
namespace App\Models;

use App\CustomAR\Role as RoleDefinition;

class Admin extends User {
    protected static $role = RoleDefinition::ADMIN;

    public function __construct() {
        $this->role_id = Role::ADMIN;
    }

    public static function select() {
        return self::getORM()->getRepository(static::class)
                             ->select()
                             ->where('role.name', self::$role);
    }
}