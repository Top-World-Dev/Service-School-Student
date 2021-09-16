<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Exam;
use App\Models\Upload;
use Cycle\ORM\Iterator;

/**
 * Exam Service
 */
class ExamService
{
    /**
     * Get all exams.
     *
     * @param array $relations
     * @return array
     */
    public function getAll($relations = []): array
    {
        $exams = Exam::getWith($relations);
        $iterator = array();
        foreach ($exams as $exam) {
            array_push($iterator, $exam->getFields());
        }
        return $iterator;
    }

    /**
     * Get all exams of a user.
     *
     * @param int $userId
     * @param array $relations
     * @return array
     */
    public function getExamsByUser(int $userId, $relations = []): array
    {
        $exams = Exam::findAll(['student_id' => $userId], $relations);
        $iterator = array();
        foreach ($exams as $exam) {
            $data = $exam->getFields();
            if (is_null($data['group_id']) || is_null($data['group']['deleted_at'])) {
                array_push($iterator, $data);
            }
        }
        return $iterator;
    }

    /**
     * Save a Exam data
     *
     * @param string $name
     */
    public function createExam(array $data): Exam
    {
        $exam = new Exam();
        $exam->__setData($data);
        $exam->save();

        return $exam;
    }

    /**
     * Save an upload data
     *
     * @param string $cloudUrl
     * @param string $type
     * @param int $examId
     * @param string $scanStatus
     * @return Upload
     */
    public function createUpload(string $cloudUrl, string $type, int $examId, string $scanStatus): Upload
    {
        $upload = new Upload();
        $upload->cloud_url = $cloudUrl;
        $upload->type = $type;
        $upload->exam_id = $examId;
        $upload->scan_status = $scanStatus;
        $upload->save();

        return $upload;
    }

    /**
     * Get a exam by ID
     *
     * @param int $id
     * @param array $relations
     * @return Exam|null
     */
    public function getExamById(int $id, array $relations = []): ?Exam
    {
        return Exam::getOneByID($id, $relations);
    }

    /**
     * Get an exam by group id
     *
     * @param int $groupId
     * @param array $relations
     * @return Exam
     */
    public function getExamByGroupId(int $groupId, array $relations = []): Exam
    {
        return Exam::findOne(['group_id' => $groupId], $relations);
    }

    /**
     * Mark an exam as verified
     *
     * @param int $id
     * @return Exam
     */
    public function markAsVerified(int $id): Exam
    {
        $exam = Exam::getOneByID($id, []);
        $exam->verified = true;
        $exam->save();
        return $exam;
    }

    /**
     * Get up to 5 matched exams.
     *
     * @param array $criteria
     * @param array $relations
     *
     */
    public function findMatches(array $criteria, $relations = [])
    {
        $exams = Exam::findAll($criteria, $relations, 5);
        return $exams;
    }

    /**
     * Get an upload by public_id(cloud_url)
     *
     * @param string $publicId
     * @param string $status
     */
    public function updateScanStatus(string $publicId, string $status)
    {
        $upload = Upload::findOne(['cloud_url' => $publicId], []);
        $upload->scan_status = $status;
        $upload->save();
    }

    /**
     * Activate an exam
     *
     * @param int $id
     */
    public function activateExam(int $id)
    {
        $exam = Exam::getOneByID($id, []);
        $exam->status = Exam::ACTIVE;
        $exam->save();
    }

    /**
     * Set average rating
     *
     * @param int $id
     * @param int $averageRating
     */
    public function setAverageExamRating(int $id, int $averageRating)
    {
        $exam = Exam::getOneByID($id);
        $exam->average_rating = $averageRating;
        $exam->save();
    }
}
