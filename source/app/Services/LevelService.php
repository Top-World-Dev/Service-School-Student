<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Level;

/**
 * Level Service
 */
class LevelService
{
    /**
     * Get all levels.
     *
     * @return array
     */
    public function getAll(): array
    {
        $levels = Level::findAll([]);
        $iterator = array();
        foreach ($levels as $level) {
            array_push($iterator, $level->getFields());
        }
        return $iterator;
    }

    /**
     * Save a discipline data
     *
     * @param string $name
     */
    public function createLevel(string $name)
    {
        $level = new Level();
        $level->name = $name;
        $level->save();
    }

    /**
     * Get a discipline by ID
     *
     * @param int $id
     * @return Level
     */
    public function getLevelById(int $id): Level
    {
        return Level::getOneByID($id);
    }
}