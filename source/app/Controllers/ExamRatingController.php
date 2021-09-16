<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ExamService;
use App\Services\ExamRatingService;
use CodeIgniter\HTTP\ResponseInterface;
class ExamRatingController extends BaseController
{
    /**
     * @var ExamService
     */
    protected $examService;

    /**
     * @var ExamRatingService
     */
    protected $examRatingService;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->examService = new ExamService();
        $this->examRatingService = new ExamRatingService();
    }

    /**
     * Get an exam rating by exam id and user id.
     *
     * @param int $examId
     * @param int $userId
     * @return mixed
     */
    public function getExamRatingByExamIdAndUserId(int $examId, int $userId)
    {
        $examRating = $this->examRatingService->getOneExamRatingByExamAndUser($examId, $userId);
        $examRatingData = $examRating->getFields();
        return $this->getResponse(['examRating' => $examRatingData]);
    }

    /**
     * Get an exams ratings by exam id.
     *
     * @param int $examId
     * @return mixed
     */
    public function getExamRatingsByExamId(int $examId)
    {
        $examRatings = $this->examRatingService->getExamRatingsByExamId($examId);
        $examsRatingData = array();
        foreach ($examRatings as $examRating) {
            array_push($examsRatingData, $examRating->getFields());
        }
        return $this->getResponse(['examRatingData' => $examsRatingData]);
    }

    /**
     * Create an exam rating
     *
     * @param int $examId
     * @param int $userId
     * @param int $stars
     * @param string $reviewBody
     * 
     */
    public function createExamRating()
    {
        $rules = [
            'exam_id' => 'required',
            'user_id' => 'required',
            'stars' => 'required|numeric',
            'review_body' => 'alpha_numeric'
        ];

        $input = array(
            'exam_id' => $this->request->getVar('exam_id'),
            'user_id' => $this->request->getVar('user_id'),
            'stars' => $this->request->getVar('stars'),
            'review_body' => $this->request->getVar('review_body')
        );

        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse(
                $this->validator->getErrors(),
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }
        $examId = $this->request->getVar('exam_id');
        $userId = $this->request->getVar('user_id');
        $stars = $this->request->getVar('stars');
        $reviewBody = $this->request->getVar('review_body');
        $response = $this->examRatingService->create($examId, $userId, $stars, $reviewBody);
        return $this->getResponse(['message' => $response->message]);
    }
}
