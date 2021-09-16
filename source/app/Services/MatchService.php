<?php declare(strict_types=1);

namespace App\Services;

use App\Models\ExamMatch;
use Cycle\ORM\Iterator;

/**
 * Match Service
 */
class MatchService
{
    /**
     * Get all paid matches
     */
    public function getPaidMatches()
    {
        $with = ['exam', 'exam.student', 'request', 'request.student', 'exam.school.country'];
        $matches = ExamMatch::findAll(['status' => 'paid', 'paid' => false], $with);
        $iterator = array();
        foreach ($matches as $match) {
            $match_data = $match->getFields();
            $match_data['payments'] = $match->getPayments();
            $match_data['payment_methods'] = $match->exam->student->getPaymentMethods();
            array_push($iterator, $match_data);
        }
        return $iterator;
    }

    /**
     * Get paid match by user and exam
     */
    public function getPaidMatchByUserAndExam(int $userId, int $examId): ExamMatch | bool
    {
        $match = ExamMatch::findOne(['status' => 'paid', 'request.student_id' => $userId, 'exam_id' => $examId]);
        if(!$match) return false;
        return $match;
    }

    /**
     * Save matches
     *
     * @param int $requestId
     * @param array $examIds
     */
    public function saveMatches(int $requestId, array $examIds)
    {
        $matches = [];
        if (!empty($examIds)) {
            foreach ($examIds as $examId) {
                $examMatch = $this->getMatchByRequestAndExam($requestId, $examId);
                if (empty($examMatch)) {
                    $examMatch = new ExamMatch();
                    $examMatch->request_id = $requestId;
                    $examMatch->exam_id = $examId;
                    $examMatch->save(false);
                }
            }
        }
    }

    /**
     * Get a match by request id and exam id.
     *
     * @param int $requestId
     * @param int $examId
     *
     * @return ExamMatch|null
     */
    public function getMatchByRequestAndExam(int $requestId, int $examId): ?ExamMatch
    {
        return ExamMatch::findOne([
            'request_id' => $requestId,
            'exam_id' => $examId
        ], []);
    }

    /**
     * Change status to selected
     *
     * @param int $id
     * @param string $status
     */
    public function updateStatus(int $id, string $status)
    {
        $match = ExamMatch::getOneByID($id, []); 
        $match->status = $status;
        $match->save();
    }

    /**
     * Save payment id
     *
     * @param int $request_id
     * @param int $exam_id
     * @param int $payment_id
     */
    public function savePaymentId(int $request_id, int $exam_id, string $payment_id)
    {
        $match = ExamMatch::findOne([
            'request_id' => $request_id,
            'exam_id' => $exam_id
        ], []);
        $match->payment_id = $payment_id;
        $match->save();
    }

    /**
     * Save payment id
     *
     * @param string $payment_id
     */
    public function paidMatch(string $payment_id)
    {
        $match = ExamMatch::findOne([
            'payment_id' => $payment_id,
        ], []);
        $match->status = 'paid';
        $match->save();
    }

    /**
     * Mark as paid by admin
     *
     * @param int $id
     */
    public function markAsPaid(int $id)
    {
        $match = ExamMatch::getOneByID($id, []); 
        $match->paid = true;
        $match->save();
    }

    /**
     * Delete matches by request id
     *
     * @param int $requestId
     */
    public function deleteByRequestId(int $requestId)
    {
        $matches = ExamMatch::findAll(['request_id' => $requestId], []); 
        foreach ($matches as $match) {
            $match->delete();
        }
    }
}