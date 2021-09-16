<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Discipline;

/**
 * Discipline Service
 */
class DisciplineService
{
    /**
     * Get all descipline.
     *
     * @return array
     */
    public function getAll(): array
    {
        $disciplines = Discipline::findAll([]);
        $iterator = array();
        foreach ($disciplines as $discipline) {
            array_push($iterator, $discipline->getFields());
        }
        return $iterator;
    }

    /**
     * Save a discipline data
     *
     * @param string $name
     */
    public function createDiscipline(string $name)
    {
        $discipline = new Discipline($name);
        $discipline->name = $name;
        $discipline->save();
    }

    /**
     * Get a discipline by ID
     *
     * @param int $id
     * @return Discipline
     */
    public function getDisciplineById(int $id): Discipline
    {
        return Discipline::getOneByID($id);
    }
}