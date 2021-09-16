<?php

namespace App\Models;

use App\CustomAR\Record;

class Role extends Record
{
    public const TABLE = 'roles';
    public const RELATIONS = [];

    // Define constants for role
    public const ADMIN     = 1;
    public const STUDENT   = 2;
    public const REVIEWER  = 3;

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
