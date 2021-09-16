<?php

namespace App\Controllers\Utils;

use App\Controllers\Admin\GroupController;
use App\Controllers\BaseController;
use App\Models\Exam;
use App\Models\Group;
use App\Models\GroupExam;
use App\Models\GroupUser;
use App\Services\GroupService;

class CronJob extends BaseController
{
    /**
     * @var int
     */
    public const DAYS_FROM_DISMISS_TO_DELETE = 15;

    /**
     * Delete exams where 'status' = EXAM::DISMISSED and
     * 'dismissed_at' date < current date.
     *
     * @return void
     */
    public static function deleteStaleMockExamSupplies()
    {
        $staleExams = Exam::findAll(['status' => Exam::SYSTEM_DISMISSED]);
        foreach ($staleExams as $staleExam) {
            $now = new \DateTime();
            $deletedAt = $staleExam->dismissed_at->add(new \DateInterval('P'.CronJob::DAYS_FROM_DISMISS_TO_DELETE.'D'));
            if ($deletedAt < $now) {
                $staleExam->delete();
            }
        }
    }

    /**
     * Dismiss stale mock exams supplied in single mode
     *
     * @return void
     */
    public static function dismissSingleModeStaleExams()
    {
        // Dismiss mock exams submitted by a single user (Single Mode) that are not in "active" status three (3) months after creation date.
        $exams = Exam::findAll(['status' => Exam::PENDING_ADMIN_REVIEW]);
        foreach ($exams as $exam) {
            if (!$exam->group_id) {
                $afterThreeMonths = $exam->created_at->add(new \DateInterval('P3M'));
                $now = new \DateTime();
                if ($afterThreeMonths < $now) {
                    $exam->status = Exam::SYSTEM_DISMISSED;
                    $exam->dismissed_at = $now;
                    $exam->save();
                }
            }
        }
    }

    /**
     * Clear groups every day
     *
     * @return void
     */
    public static function clearGroups()
    {
        $staleGroups = Group::findAll(['status' => Group::STATUS_SYSTEM_DISMISSED]);
        foreach ($staleGroups as $staleGroup) {
            $now = new \DateTime();
            $deletedAt = $staleGroup->dismissed_at->add(new \DateInterval('P'.CronJob::DAYS_FROM_DISMISS_TO_DELETE.'D'));
            if ($deletedAt < $now) {
                GroupService::delete($staleGroup->id);
            }
        }
    }

    /**
     * Dismiss groups every day
     *
     * @return void
     */
    public static function dismissGroups()
    {
        // Dismiss the groups that has less than 3 members in 24 hours after it was created.
        $groups = Group::findAll(['status' => 'initialized']);
        foreach ($groups as $group) {
            $groupId = $group->id;
            $afterDay = $group->created_at->add(new \DateInterval('P1D'));
            $now = new \DateTime();
            if ($afterDay < $now) {
                $group->status = Group::STATUS_SYSTEM_DISMISSED;
                $exam->dismissed_at = $now;
                $group->save();
            }
        }

        // Delete the groups if a group is not in "pending" status 72 hours after it was created
        $groups = Group::findAll(['status' => ['initialized', 'progress']]);
        foreach ($groups as $group) {
            $after3Day = $group->created_at->add(new \DateInterval('P3D'));
            $groupId = $group->id;
            $now = new \DateTime();
            if ($after3Day < $now) {
                $group->status = Group::STATUS_SYSTEM_DISMISSED;
                $exam->dismissed_at = $now;
                $group->save();
            }
        }

        // Dismiss the groups that didn't resubmit in 48 hours after it was rejected.
        $groups = Group::findAll(['status' => Group::STATUS_IN_PROGRESS]);
        foreach ($groups as $group) {
            $groupId = $group->id;
            $afterTwoDay = $group->rejected_at->add(new \DateInterval('P2D'));
            $now = new \DateTime();
            if ($afterTwoDay < $now) {
                $group->status = Group::STATUS_SYSTEM_DISMISSED;
                $exam->dismissed_at = $now;
                $group->save();
            }
        }
    }

    /**
     * Update all group's status
     *
     * @return void
     */
    public static function updateGroupsStatus()
    {
        $groups = Group::findAll([]);
        foreach ($groups as $group) {
            GroupService::updateGroupStatus($group->id);
        }
    }
}
