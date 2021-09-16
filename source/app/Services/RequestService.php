<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Request;

/**
 * Request Service
 */
class RequestService
{

    /**
     * Get all requests.
     *
     * @param array $relations
     * @return array
     */
    public function getAll($relations = []): array
    {
        $requests = Request::getWith($relations);
        $iterator = array();
        foreach ($requests as $request) {
            array_push($iterator, $request->getFields());
        }
        return $iterator;
    }

    /**
     * Get requests of a user.
     *
     * @param int $userId
     * @param array $relations
     * @return array
     */
    public function getByUser(int $userId, $relations = []): array
    {
        $requests = Request::findAll(['student_id' => $userId], $relations);
        $iterator = array();
        foreach ($requests as $request) {
            $requestData = $request->getFields();
            $requestData['exams'] = $request->getExams();
            array_push($iterator, $requestData);
        }
        return $iterator;
    }

    /**
     * Get a request by ID
     *
     * @param int $id
     * @param array $relations
     * @return Exam
     */
    public function getById(int $id, array $relations = []): Request
    {
        return Request::getOneByID($id, $relations); 
    }

    /**
     * Save a request data
     *
     * @param int $studentId
     * @param int $disciplineId
     * @param int $levelId
     * @param int $subjectId
     * @param string|null $examDate
     * @param string|null $examNumber
     * @param int|null $duration
     * @param int|null $semester
     * @param int|null $delay
     * @param string|null $courseNum
     * @param bool $otherSchool
     * @param bool $otherSemester
     * @param bool $otherProfessor
     * @param int $schoolId
     * @param int|null $professorId,
     * @param string|null $plan
     * @param string|null $year
     *
     * @return Request
     */
    public function create(int $studentId, int $disciplineId, int $levelId, int $subjectId, ?string $examDate, string $examNumber, ?int $duration, ?int $semester, ?int $delay, ?string $courseNum, bool $otherSchool, bool $otherSemester, bool $otherProfessor, bool $schoolId, ?int $professorId, ?string $plan = null, ?string $year = null): Request
    {
        $request = new Request();
        $request->student_id = $studentId;
        $request->discipline_id = $disciplineId;
        $request->level_id = $levelId;
        $request->subject_id = $subjectId;
        $request->exam_date = $examDate;
        $request->exam_number = $examNumber;
        $request->exam_duration = $duration;
        $request->semester = $semester;
        $request->delay = $delay;
        $request->course_number = $courseNum;
        $request->other_school = $otherSchool;
        $request->other_semester = $otherSemester;
        $request->other_professor = $otherProfessor;
        $request->school_id = $schoolId;
        $request->professor_id = $professorId;
        $request->plan = $plan;
        $request->year = $year;
        $request->save();

        return $request;
    }

    /**
     * Get a request by ID
     *
     * @param int $id
     * @return Request
     */
    public function getRequestById(int $id): Request
    {
        return Request::getOneByID($id);
    }

    /**
     * Dismiss a request
     *
     * @param int $id
     */
    public function dismiss(int $id)
    {
        $request = Request::getOneByID($id);
        $request->dismissed_at = new \DateTimeImmutable();
        $request->save();
    }

    /**
     * Delete a request
     *
     * @param int $id
     */
    public function delete(int $id)
    {
        $request = Request::getOneByID($id);
        $request->delete();
    }
}