<?php

namespace App\Database\Seeds;

use App\Models\Exam;
use CodeIgniter\Database\Seeder;

class RequestSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('requests');

        helper('date');
        $now = date('Y-m-d H:i:s', now());

        $data = [
            [
                'student_id' => 5,
                'discipline_id' => 1,
                'level_id' => 1,
                'subject_id' => 1,
                'professor_id' => 1,
                'exam_duration' => 1,
                'exam_date' => '2021-06-05 00:00:00',
                'exam_number' => 1,
                'semester' => 1,
                'delay' => 3,
                'school_id' => 1,
                'exam_number' => 1,
                'plan' => Exam::PLAN_STANDARD,
                'year' => '2021',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'student_id' => 6,
                'discipline_id' => 1,
                'level_id' => 1,
                'subject_id' => 2,
                'professor_id' => 1,
                'exam_duration' => 1,
                'exam_date' => '2021-06-05 00:00:00',
                'exam_number' => 1,
                'semester' => 1,
                'delay' => 3,
                'school_id' => 1,
                'exam_number' => 1,
                'plan' => Exam::PLAN_STANDARD,
                'year' => '2021',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        $builder->insertBatch($data);
    }
}
