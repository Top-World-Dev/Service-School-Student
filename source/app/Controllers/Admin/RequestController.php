<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Request;
use App\Services\RequestService;
use App\Services\ExamService;
use App\Services\MatchService;
use CodeIgniter\HTTP\ResponseInterface;

class RequestController extends BaseController
{
    /**
     * @var RequestService
     */
    protected $requestService;

    /**
     * @var ExamService
     */
    protected $examService;

    /**
     * @var MatchService
     */
    protected $matchService;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->requestService = new RequestService();
        $this->examService = new ExamService();
        $this->matchService = new MatchService();
    }

    /**
     * Get a request by Id.
     *
     * @return mixed
     */
    public function getById(int $id)
    {
        $with = ['discipline', 'level', 'subject', 'student', 'school'];
        $request = $this->requestService->getById($id, $with);
        $requestData = $request->getFields();
        $requestData['matches'] = $request->getExamsForAdmin();
        return $this->getResponse(['request' => $requestData]);
    }

	/**
     * Find up to 5 matched exams.
     *
     * @return mixed
     */
    public function findMatch(int $id)
    {
        $request = $this->requestService->getById($id, []);
        $criteria = [
            'discipline_id' => $request->discipline_id,
            'level_id' => $request->level_id,
            'subject_id' => $request->subject_id,
        ];

        if (!$request->other_school) {
            $criteria['school_id'] = $request->school_id;
        }
        if (!$request->other_semester && $request->semester) {
            $criteria['semester'] = [ $request->semester, null ];
        }
        if (!$request->other_professor && $request->professor_id) {
            $criteria['professor_id'] = $request->professor_id;
        }
        if ($request->course_number) {
            $criteria['course_number'] = [
                'noSpecialInputChar' => [ strtolower($request->course_number), null ]
            ];
        }
        if ($request->exam_number) {
            $criteria['exam_number'] = [ $request->exam_number, null ];
        }
        if ($request->exam_duration) {
            $criteria['exam_duration'] = [
                '<=' => $request->exam_duration,
                '=' => null
            ];
        }
        if ($request->year) {
            if ($request->year_condition == Request::YEAR_CONDITION_AFTER) {
                $criteria['exam_date'] = ['year.gt' => $request->year];
            } else if($request->year_condition == Request::YEAR_CONDITION_BEFORE) {
                $criteria['exam_date'] = ['year.lt' => $request->year];
            } else {
                $criteria['exam_date'] = ['year' => $request->year];
            }
        }
        if ($request->plan) {
            $criteria['plan'] = $request->plan;
        }
        if ($request->min_star_rating || $request->max_star_rating) {
            $rates = range($request->min_star_rating, $request->max_star_rating);
            $criteria['average_rating'] = $rates;
        }

        $exams = $this->examService->findMatches($criteria, []);
        $examsIds = [];
        $examsData = [];
        foreach ($exams as $exam) {
            $examsIds[] = $exam->id;
            $examsData[] = $exam->getFields();
        }
        $this->matchService->saveMatches($request->id, $examsIds);

        return $this->getResponse(['examsData' => $examsData]);
    }

    /**
     * Assign the matched exam to user's request
     *
     * @return mixed
     */
    public function assignExam()
    {
        $input = $this->getRequestInput($this->request);

        $this->matchService->updateStatus($input['id'], 'selected');
        return $this->getResponse(['status' => true]);
    }
}