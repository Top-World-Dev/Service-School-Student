<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamReview;

/**
 * ExamReview Service
 */
class ExamReviewService
{
    /**
     * Create an exam review
     *
     * @param int $examId,
     * @param int $reviewerId
     * @return ExamReview
     */
    public function create(int $examId, int $reviewerId): ExamReview
    {
        // Validate duplication
        $examReview = $this->getOneExamAndReviewer($examId, $reviewerId);
        if (empty($examReview)) {
            $examReview = new ExamReview();
            $examReview->exam_id = $examId;
            $examReview->reviewer_id = $reviewerId;
            $examReview->status = ExamReview::PENDING_SME_REVIEW;
            $examReview->save();

            // update exam's status
            $exam = Exam::getOneByID($examId, []);
            $exam->status = ExamReview::PENDING_SME_REVIEW;
            $exam->save();
        }
        return $examReview;
    }

    /**
     * Get one by exam and reviewer
     *
     * @param int $examId,
     * @param int $reviewerId
     * @return ExamReview|null
     */
    public function getOneExamAndReviewer(int $examId, int $reviewerId): ?ExamReview
    {
        return ExamReview::findOne(['exam_id' => $examId, 'reviewer_id' => $reviewerId], []);
    }
}
