<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ExamService;
use CodeIgniter\HTTP\ResponseInterface;

class ExamController extends BaseController
{
    /**
     * @var ExamService
     */
    protected $examService;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->examService = new ExamService();
    }

    /**
     * Get an exam by Id.
     *
     * @return mixed
     */
    public function getById(int $id)
    {
        $withRelations = ['discipline', 'level', 'subject', 'professor', 'student', 'school', 'group'];
        $exam = $this->examService->getExamById($id, $withRelations);
        $examData = $exam->getFields();
        $examData['uploads'] = $exam->getUploads();
        return $this->getResponse(['exam' => $examData]);
    }

    /**
     * Activate an exam
     *
     * @param int $examId
     * @return mixed
     */
    public function activateExam(int $examId)
    {
        $this->examService->activateExam($examId);
        return $this->getResponse(['message' => 'Exam is activated successfully']);
    }
}
