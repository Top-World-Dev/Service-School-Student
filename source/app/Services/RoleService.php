<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Role;

/**
 * Role Service
 */
class RoleService
{
    /**
     * Save a role data
     *
     * @param string $name
     */
    public function createRole(string $name)
    {
        $role = new Role();
        $role->name = $name;
        $role->save();
    }

    /**
     * Get a role by ID
     *
     * @param int $id
     * @return Role
     */
    public function getRoleById(int $id): Role
    {
        return Role::find()->findByPK($id);
    }
}