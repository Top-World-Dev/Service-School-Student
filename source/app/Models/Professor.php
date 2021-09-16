<?php
namespace App\Models;

// use App\CustomAR\Role as RoleDefinition;
use App\CustomAR\Record;
use App\CustomAR\Relation;

class Professor extends Record {
    public const TABLE = 'professors';
    public const RELATIONS = [];

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'www_url' => $this->www_url,
            'email' => $this->email,
        ];
    }
}