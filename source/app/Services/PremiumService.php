<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamContent;
use App\Models\ExamReview;
use Spiral\Database\Injection\Parameter;

/**
 * Premium Service
 */
class PremiumService extends BaseService
{
    /**
     * @var GroupService
     */
    protected $groupService;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->groupService = new GroupService();
    }

    /**
     * Get all reviewable exams of user.
     *
     * @param int $userId
     * @return array
     */
    public function getReviewableExams(int $userId): array
    {
        $examReviews = $this->iteratorToArray(ExamReview::findAll(['reviewer_id' => $userId]));
        $result = [];
        $examIds = $this->mapArray($examReviews, 'exam_id');
        if (!empty($examIds)) {
            $exams = Exam::findAll(['id' => ['in' => new Parameter($examIds)]]);
            foreach ($exams as $exam) {
                $examData = $exam->getFields();
                if (!empty($examData['group_id'])) {
                    $groupUsers = $this->groupService->getGroupUsers($examData['group_id']);
                    // $examData['members'] = $groupUsers;
                    $examData['qas'] = $this->groupService->getQAsByGroupId($examData['group_id']);
                } else {
                    $examData['uploads'] = $exam->getUploads();
                }
                array_push($result, $examData);
            }
        }
        return $result;
    }

    /**
     * Check if exam's plan can be updated.
     *
     * @param int $examId
     * @return bool
     */
    public function checkSetPlanAvailability(int $examId): bool
    {
        $examReviews = ExamReview::findAll(['exam_id' => $examId, 'status' => [ExamReview::SME_UNCHANGED, ExamReview::SME_MODIFIED, ExamReview::SME_REJECTED]]);

        if (iterator_count($examReviews) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Set exam's plan
     *
     * @param int $examId
     * @param string $status // "origin", "revision", "reject"
     */
    public function setExamPlan(int $examId, string $status)
    {
        $exam = Exam::getOneByID($examId);

        if ($status == ExamReview::SME_REJECTED) {
            $exam->status = Exam::ADMIN_REJECTED;
            $exam->save();
        } elseif ($status == ExamReview::SME_UNCHANGED) {
            $exam->status = Exam::PENDING_ADMIN_REVIEW;
            $exam->plan = Exam::PLAN_PREMINUM;
            $exam->save();
        } elseif ($status == ExamReview::SME_MODIFIED) {
            // clone new exam
            $newExam = new Exam();
            $examData = $exam->getFields();
            unset($examData['id']);
            $newExam->__setData($examData);
            $newExam->status = Exam::PENDING_ADMIN_REVIEW;
            $newExam->plan == Exam::PLAN_PREMINUM;
            $newExam->save();
            $newExamId = $newExam->id;

            // disable origin exam
            $exam->status = Exam::ADMIN_REJECTED;
            $exam->save();

            return $newExamId;
        }
    }

    /**
     * Add a question & solution for new exam
     *
     * @param int $reviewerId
     * @param int $examId
     * @param array $qa
     */
    public function addQA(int $reviewerId, int $examId, array $qa)
    {
        $examContent = new ExamContent();
        $examContent->reviewer_id = $reviewerId;
        $examContent->exam_id = $examId;
        $examContent->question = $qa['question'];
        $examContent->solution = $qa['solution'];
        $examContent->save();
    }
}
