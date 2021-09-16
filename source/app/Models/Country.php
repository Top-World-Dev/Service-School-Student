<?php
namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;

class Country extends Record {
    public const TABLE = 'countries';
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
            'country_name' => $this->country_name,
            'currency' => $this->currency,
            'code' => $this->code,
        ];
    }
}