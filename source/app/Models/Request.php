<?php
namespace App\Models;

use App\Models\ExamMatch;
use App\CustomAR\Record;
use App\CustomAR\Relation;
use Cycle\ORM\Promise\Reference;

class Request extends Record {
    public const TABLE = 'requests';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
            'discipline' => Discipline::class,
            'subject'    => Subject::class,
            'level'      => Level::class,
            'professor'  => Professor::class,
            'exam'       => Exam::class,
            'school'     => School::class,
            'student'    => Student::class,
        ],
    ];

    // Define year conditions
    public const YEAR_CONDITION_BEFORE = 'before';
    public const YEAR_CONDITION_AFTER  = 'after';

    /**
     * Get matches
     *
     * @return array
     */
    public function getExams(): array
    {
        $criteria = [
            'request_id' => $this->id,
            'status' => ['selected', 'paid']
        ];
        $with = [];
        $data = ExamMatch::findAll($criteria, $with);

        $matches = array();
        foreach ($data as $item) {
            $match = $item->getFields();
            $exam = Exam::getOneByID($match['exam_id'], []);
            $match['exam'] = $exam->getFields();
            $match['uploads'] = $exam->getUploads(true);
            array_push($matches, $match);
        }
        return $matches;
    }

    /**
     * Get matches
     *
     * @return array
     */
    public function getExamsForAdmin(): array
    {
        $data = ExamMatch::findAll(['request_id' => $this->id], ['exam', 'exam.student']);

        $matches = array();
        foreach ($data as $item) {
            array_push($matches, $item->getFields());
        }
        return $matches;
    }

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        $request = [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'exam_duration' => $this->exam_duration,
            'course_number' => $this->course_number,
            'exam_date' => $this->exam_date,
            'delay' => $this->delay,
            'paid' => $this->paid,
            'minStarRating' => $this->min_star_rating,
            'maxStarRating' => $this->max_star_rating,
            'dismissed_at' => $this->dismissed_at,
            'created_at' => $this->created_at,
        ];

        if (get_class($this->discipline) !== Reference::class) {
            $request['discipline'] = $this->discipline->getFields();
        }
        if (get_class($this->subject) !== Reference::class) {
            $request['subject'] = $this->subject->getFields();
        }
        if (get_class($this->level) !== Reference::class) {
            $request['level'] = $this->level->getFields();
        }
        if (get_class($this->student) !== Reference::class) {
            $request['student'] = $this->student->getFields();
        }
        if (get_class($this->school) !== Reference::class) {
            $request['school'] = $this->school->getFields();
        }

        return $request;
    }
}