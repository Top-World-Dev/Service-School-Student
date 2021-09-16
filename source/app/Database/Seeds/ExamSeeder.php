<?php

namespace App\Database\Seeds;

use App\Models\Exam;
use CodeIgniter\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('exams');

        helper('date');
        $now = date('Y-m-d H:i:s', now());

        $data = [
            [
                'summary' => 'This is first single uploaded exam by Jack Smith',
                'grade_value' => 95,
                'verified' => true,
                'discipline_id' => 1,
                'level_id' => 1,
                'subject_id' => 1,
                'student_id' => 2,
                'professor_id' => 1,
                'school_id' => 1,
                'exam_date' => '2021-06-05 00:00:00',
                'semester' => 1,
                'plan' => Exam::PLAN_STANDARD,
                'status' => Exam::PENDING_ADMIN_REVIEW,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'summary' => 'This is first single uploaded exam by Eli Bernan',
                'grade_value' => 93,
                'verified' => true,
                'discipline_id' => 1,
                'level_id' => 1,
                'subject_id' => 2,
                'student_id' => 3,
                'professor_id' => 1,
                'school_id' => 1,
                'exam_date' => '2021-06-01 00:00:00',
                'semester' => 1,
                'plan' => Exam::PLAN_STANDARD,
                'status' => Exam::PENDING_ADMIN_REVIEW,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'summary' => 'This is first single uploaded exam by Mark Stu',
                'grade_value' => 96,
                'verified' => true,
                'discipline_id' => 1,
                'level_id' => 1,
                'subject_id' => 3,
                'student_id' => 4,
                'professor_id' => 1,
                'school_id' => 1,
                'exam_date' => '2021-06-01 00:00:00',
                'semester' => 1,
                'plan' => Exam::PLAN_STANDARD,
                'status' => Exam::PENDING_ADMIN_REVIEW,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        $builder->insertBatch($data);
    }
}
