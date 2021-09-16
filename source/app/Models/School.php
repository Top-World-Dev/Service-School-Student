<?php
namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;
use Cycle\ORM\Promise\Reference;

class School extends Record {
    public const TABLE = 'schools';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
            'country' => Country::class,
        ],
    ];

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        $school = [
            'id' => $this->id,
            'school_name' => $this->school_name,
            'school_abbreviation' => json_decode($this->school_abbreviation),
            'school_url' => $this->school_url,
            'school_city' => $this->school_city,
            'school_state' => $this->school_state,
            'offer_email' => $this->offer_email,
        ];

        if(get_class($this->country) !== Reference::class) {
            $school['country'] = $this->country->getFields();
        }

        return $school;
    }
}