<?php

declare(strict_types=1);

namespace App\Services;

use App\CustomAR\Record;
use App\Models\User;
use App\Models\Professor;

/**
 * User Service
 */
class ProfessorService
{
    /**
     * Save a role data
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $wwwUrl
     * @param bool $verified
     * @return Professor
     *
     */
    public function create(string $firstName, string $lastName, string $email, string $wwwUrl, bool $verified = false): Professor
    {
        $user = new Professor();
        $user->email = $email;
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->www_url = $wwwUrl;
        $user->verified = $verified;
        $user->save();

        return $user;
    }

    /**
     * Update a professor
     *
     * @param int $id
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $wwwUrl
     * @param bool $verified
     * @return Professor
     */
    public function update(int $id, string $firstName, string $lastName, string $email, string $wwwUrl, bool $verified = false): Professor
    {
        $user = Professor::getOneByID($id);
        $user->id = $id;
        $user->email = $email;
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->www_url = $wwwUrl;
        $user->verified = $verified;
        $user->save();

        return $user;
    }

    /**
     * Get a user by ID
     *
     * @param int $id
     * @return Professor
     */
    public function getUserById(int $id): Professor
    {
        return Professor::getOneByID($id);
    }

    /**
     * Get a professor by email.
     *
     * @param string $email
     *
     * @return Professor|null
     */
    public function findOneOrNullByEmail(string $email): ?Professor
    {
        return Professor::findOne(['email' => $email]);
    }

    /**
     * Get all professors
     *
     * @return array
     */
    public function getProfessors(): array
    {
        $professors = Professor::findAll([]);

        $iterator = array();
        foreach ($professors as $professor) {
            array_push($iterator, $professor->getFields());
        }
        return $iterator;
    }
}
