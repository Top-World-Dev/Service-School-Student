<?php

namespace App\Controllers;

use App\Services\PremiumService;
use CodeIgniter\HTTP\ResponseInterface;

class ReviewerController extends BaseController
{
    /**
     * @var PremiumService
     */
    protected $premiumService;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->premiumService = new PremiumService();
    }

    /**
     * Get exams for review
     *
     * @return mixed
     */
    public function getAvailableExams()
    {
        $reviews = $this->premiumService->getReviewableExams($this->user['id']);

        return $this->getResponse([
                        'reviews' => $reviews,
                    ]);
    }

    /**
     * Submit reviewed result
     *
     * @return mixed
     */
    public function reviewExam()
    {
        // Validate request parameters.
        $rules = [
            'exam_id' => 'required|numeric',
            'result' => 'required',
        ];

        $input = $this->getRequestInput($this->request);

        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse(
                $this->validator->getErrors(),
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        if (!$this->premiumService->checkSetPlanAvailability($input['exam_id'])) {
            return $this->getResponse(
                ['message' => "You can't update the exam's status"],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        $examId = $this->premiumService->setExamPlan($input['exam_id'], $input['result'], $input['questions_and_solutions']);

        // Add new questions and solutions
        if (!empty($examId) && !empty($input['questions_and_solutions'])) {
            $questions_and_solutions = $input['questions_and_solutions'];
            foreach ($questions_and_solutions as  $questions_and_solution) {
                $this->premiumService->addQA($this->user['id'], $examId, $questions_and_solution);
            }
        }

        return $this->getResponse([
                        'message' => "Exam's plan is updated successfully"
                    ]);
    }
}
