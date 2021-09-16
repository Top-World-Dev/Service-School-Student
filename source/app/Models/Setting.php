<?php
namespace App\Models;

use App\CustomAR\Record;

class Setting extends Record {
    public const TABLE = 'settings';
    public const RELATIONS = [];

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        $setting = [
            'id'    => $this->id,
            'name'  => $this->name,
            'value' => $this->value
        ];

        if ($this->type === 'array') {
            $setting['value'] = json_decode($this->value, true);
        }

        return $setting;
    }
}