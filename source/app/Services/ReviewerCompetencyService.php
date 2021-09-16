<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ReviewerCompetency;

/**
 * ReviewerCompetency Service
 */
class ReviewerCompetencyService
{
    /**
     * Get all by competency.
     *
     * @param int $discipline_id
     * @param int $level_id
     * @param int $subject_id
     * @return array
     */
    public function getAllByCompetency(int $discipline_id, int $level_id, int $subject_id): array
    {
        $competencies = ReviewerCompetency::findAll([
            'disciplines' => ['like' => '%' . $discipline_id . '%'],
            'levels' => ['like' => '%' . $level_id . '%'],
            'subjects' => ['like' => '%' . $subject_id . '%']
        ]);
        $iterator = array();
        foreach ($competencies as $competency) {
            $data = $competency->getFields();
            if (in_array($discipline_id, $data['disciplines']) &&
               in_array($level_id, $data['levels']) &&
               in_array($subject_id, $data['subjects'])
            ) {
                array_push($iterator, $data);
            }
        }
        return $iterator;
    }
}
