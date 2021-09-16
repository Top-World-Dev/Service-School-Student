<?php

namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;
use Cycle\ORM\Promise\Reference;
use App\Libraries\CloudinaryLibrary;
use Spiral\Database\Injection\Parameter;

class Exam extends Record
{
    public const TABLE = 'exams';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
            'discipline' => Discipline::class,
            'subject'    => Subject::class,
            'level'      => Level::class,
            'professor'  => Professor::class,
            'student'    => Student::class,
            'school'     => School::class,
            'group'      => Group::class,
        ],
    ];

    // Define constants for status
    public const PENDING_ADMIN_REVIEW = 'pending-admin-review';
    public const ACTIVE  = 'active'; // available for matching to any active mock exam request
    public const ADMIN_REJECTED  = 'admin-rejected';
    public const SYSTEM_DISMISSED = 'system-dismissed';  // marked for deletion by the system/cronjob

    // Define constants for plan
    public const PLAN_STANDARD = 'standard';
    public const PLAN_PREMINUM = 'premium';
    public const PLAN_MIXED    = null;

    /**
     * Get uploads
     *
     * @param bool $onlyMock
     * @return array
     */
    public function getUploads($isMock = false): array
    {
        $select = $this->getORM()->getRepository(Upload::class)
                              ->select()
                              ->where('exam_id', $this->id);

        if ($isMock) {
            $select->where('type', 'in', new Parameter(['u_mock', 'g_mock']));
        }

        $data = $select->fetchData();
        $iterator = self::getIterator(Upload::class, $data);
        $uploads = array();
        foreach ($iterator as $iterator) {
            array_push($uploads, $iterator->getFields());
        }
        return $uploads;
    }

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        $exam = [
            'id' => $this->id,
            'summary' => $this->summary,
            'grade_value' => $this->grade_value,
            'verified' => $this->verified,
            'discipline_id' => $this->discipline_id,
            'level_id' => $this->level_id,
            'subject_id' => $this->subject_id,
            'student_id' => $this->student_id,
            'exam_date' => $this->exam_date,
            'course_number' => $this->course_number,
            'semester' => $this->semester,
            'ungraded_sample_url' => $this->ungraded_sample_url,
            'graded_sample_url' => $this->graded_sample_url,
            'group_id' => $this->group_id,
            'professor_id' => $this->professor_id,
            'school_id' => $this->school_id,
            'plan' => $this->plan,
            'averageRating' => $this->average_rating,
            'status' => $this->status
        ];

        if (get_class($this->professor) !== Reference::class) {
            $exam['professor'] = $this->professor->getFields();
        }
        if (get_class($this->discipline) !== Reference::class) {
            $exam['discipline'] = $this->discipline->getFields();
        }
        if (get_class($this->subject) !== Reference::class) {
            $exam['subject'] = $this->subject->getFields();
        }
        if (get_class($this->level) !== Reference::class) {
            $exam['level'] = $this->level->getFields();
        }
        if (get_class($this->student) !== Reference::class) {
            $exam['student'] = $this->student->getFields();
        }
        if (get_class($this->school) !== Reference::class) {
            $exam['school'] = $this->school->getFields();
        }
        if ($this->group && get_class($this->group) !== Reference::class) {
            $exam['group'] = $this->group->getFields();
        }

        return $exam;
    }
}
