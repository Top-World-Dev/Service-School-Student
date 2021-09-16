<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Subject;

/**
 * Subject Service
 */
class SubjectService
{
    /**
     * Get all levels.
     *
     * @return array
     */
    public function getAll(): array
    {
        $subjects = Subject::findAll([]);
        $iterator = array();
        foreach ($subjects as $subject) {
            array_push($iterator, $subject->getFields());
        }
        return $iterator;
    }

    /**
     * Save a discipline data
     *
     * @param string $name
     */
    public function createSubject(string $name)
    {
        $subject = new Subject();
        $subject->name = $name;
        $subject->save();
    }

    /**
     * Get a discipline by ID
     *
     * @param int $id
     * @return Subject
     */
    public function getSubjectById(int $id): Subject
    {
        return Subject::getOneByID($id);
    }
}