<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExam extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'summary'       => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'grade_value'       => [
                'type'       => 'DECIMAL',
                'constraint' => '5,3',
                'null'       => true
            ],
            'verified'       => [
                'type'       => 'BOOL',
                'default'    => false
            ],
            'discipline_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'       => true,
            ],
            'level_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'       => true,
            ],
            'subject_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'       => true,
            ],
            'student_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'       => true,
            ],
            'group_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true
            ],
            'professor_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'       => true,
            ],
            'school_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'       => true,
            ],
            'ungraded_sample_url'       => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true
            ],
            'graded_sample_url'       => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true
            ],
            'exam_duration'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => true
            ],
            'course_number'  => [
                'type'       => 'VARCHAR',
                'constraint' => '15',
                'null'       => true
            ],
            'exam_date'  => [
                'type'       => 'DATETIME',
                'null'       => true
            ],
            'exam_number'  => [
                'type'       => 'ENUM("1", "2", "Midterm", "3", "4", "Final")',
                'null'       => true
            ],
            'semester'      => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => true
            ],
            'plan'  => [
                'type'       => 'ENUM("standard", "premium")',
                'default'    => "standard",
                'null'       => true
            ],
            'average_rating'    => [
                'type'       => 'INT',
                'constraint' => 1,
                'null'       => true
            ],
            'status'  => [
                'type'       => 'ENUM("pending-admin-review", "active", "review", "admin-rejected", "system-dismissed")',
                'default'    => "pending-admin-review",
                'null'       => true
            ],
            'dismissed_at'     => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'created_at'       => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at'       => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'deleted_at'       => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('discipline_id','disciplines','id');
        $this->forge->addForeignKey('level_id','levels','id');
        $this->forge->addForeignKey('subject_id','subjects','id');
        $this->forge->addForeignKey('student_id','users','id');
        $this->forge->addForeignKey('group_id','groups','id');
        $this->forge->addForeignKey('professor_id','professors','id');
        $this->forge->addForeignKey('school_id','schools','id');
        $this->forge->createTable('exams');
    }

    public function down()
    {
        $this->forge->dropTable('exams');
    }
}
