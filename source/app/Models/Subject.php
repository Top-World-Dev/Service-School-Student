<?php
namespace App\Models;

use App\CustomAR\Record;

class Subject extends Record {
    public const TABLE = 'subjects';
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
            'name' => $this->name
        ];
    }
}