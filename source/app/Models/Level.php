<?php
namespace App\Models;

use App\CustomAR\Record;

class Level extends Record {
    public const TABLE = 'levels';
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