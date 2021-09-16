<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExamRating;
use App\Services\ExamService;
use App\Services\RequestService;
use App\Services\MatchService;

/**
 * examRating Service
 */
class ExamRatingService
{
    /**
     * Create an exam review
     *
     * @param int $examId,
     * @param int $userId
     * @return ExamRating
     */

    /**
     * @var ExamService
     */
    protected $examService;

    /** 
     * @var RequestService
    */
    protected $requestService;

    /** 
     * @var MatchService
     */
    protected $matchService;

    /**
     * Number of days within which a rating may be submitted after an exam has been purchased
     */
    public const ALLOCATED_RATING_DAYS = 12;


    public function __construct()
    {
        $this->examService = new ExamService();
        $this->requestService = new RequestService();
        $this->matchService = new MatchService();
    }


    public function create(int $examId, int $userId, int $stars, string $reviewBody): \stdClass 
    {
        $result = new \stdClass;
        // check duplication
        $examRating = $this->getOneExamRatingByExamAndUser($examId, $userId);
        // check if mock exam was paid 
        $paidMatch = $this->matchService->getPaidMatchByUserAndExam($userId, $examId);
        // check if mock exam can be rated
        $canBeRated = $this->canBeRated($paidMatch);

        if (!empty($examRating)) {
            $result->examRating = null;
            $result->message = "Rating not allowed: duplicate rating";
        } elseif (!$paidMatch) {
            $result->examRating = null;
            $result->message = "Rating not allowed: exam not paid";
        } elseif (!$canBeRated) {
            $result->examRating = null;
            $result->message = "Rating not allowed: exam was paid more than 12 days ago";
        } else {
            $examRating = new ExamRating();
            $examRating->exam_id = $examId;
            $examRating->user_id = $userId;
            $examRating->stars = $stars;
            $examRating->review_body = $reviewBody;
            $examRating->save();
            // set new rating for mock exam
            $averageRating = $this->getAverageRatingByExamId($examId);
            $this->examService->setAverageExamRating($examId, $averageRating);

            $result->examRating = $examRating;
            $result->message = "Exam rating successfully created";
        }
        return $result;
    }


    /**
     * Get average rating of one exam
     * 
     * @param int $examId
     * @return int
     */
    public function getAverageRatingByExamId($examId)
    {
        $criteria = ["exam_id" => $examId];
        $relatedExamRatings = ExamRating::findAll($criteria);
        $starCount = iterator_count($relatedExamRatings);
        $starAvg = 0;
        foreach ($relatedExamRatings as $relatedExamRating) {
            $starAvg = $starAvg + $relatedExamRating->stars;
        }
        $starAvg = $starAvg / $starCount;
        return (int) $starAvg;
    }

    /**
     * Get one by exam and user
     *
     * @param int $examId,
     * @param int $userId
     * @return examRating|null
     */
    public function getOneExamRatingByExamAndUser(int $examId, int $userId): ?ExamRating
    {
        return ExamRating::findOne(['exam_id' => $examId, 'user_id' => $userId], []);
    }

    /**
     * Get exams rating by exam
     *
     * @param int $examId,
     * 
     */
    public function getExamRatingsByExamId(int $examId)
    {
        $criteria = ["exam_id" => $examId];
        $examRatings = ExamRating::findAll($criteria);
        return $examRatings;
    }

    private function canBeRated($match)
    {
        $differenceFormat = '%a';
        $date = date_create();
        $interval = $match->updated_at->diff($date)->format($differenceFormat);

        return ($interval <= self::ALLOCATED_RATING_DAYS) ? true : false;
    }
}
