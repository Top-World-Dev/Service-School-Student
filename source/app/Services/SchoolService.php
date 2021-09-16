<?php declare(strict_types=1);

namespace App\Services;

use App\Models\School;

/**
 * School Service
 */
class SchoolService extends BaseService
{
    /**
     * Get all schools.
     *
     * @return array
     */
    public function getAll(): array
    {
        $schools = School::findAll([]);
        return self::iteratorToArray($schools);
    }

    /**
     * Get schools by country.
     *
     * @param int $countryId
     * @return array
     */
    public function getByCountry($countryId): array
    {
        $schools = School::findAll(['country_id' => $countryId]);
        return self::iteratorToArray($schools);
    }

    /**
     * Get a school by ID
     *
     * @param int $id
     * @param array $relations
     * @return School|null
     */
    public function getById(int $id, array $relations = []): ?School
    {
        return School::getOneByID($id, $relations);
    }
}