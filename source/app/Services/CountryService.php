<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Country;

/**
 * Country Service
 */
class CountryService
{
    /**
     * Get all countries.
     *
     * @return array
     */
    public function getAll(): array
    {
        $counries = Country::findAll([]);
        $iterator = array();
        foreach ($counries as $country) {
            array_push($iterator, $country->__getData());
        }
        return $iterator;
    }
}