<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRequest extends Migration
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
            'student_id'     => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
            ],
            'discipline_id'  => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'       => true,
            ],
            'level_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
            ],
            'subject_id'     => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
            ],
            'professor_id'   => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
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
            'exam_date'      => [
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
            'delay'      => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => true
            ],
            'other_school'           => [
                'type'       => 'BOOL',
                'default'    => false
            ],
            'other_semester'           => [
                'type'       => 'BOOL',
                'default'    => false
            ],
            'other_professor'           => [
                'type'       => 'BOOL',
                'default'    => false
            ],
            'exam_id'        => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true
            ],
            'school_id'      => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true
            ],
            'paid'           => [
                'type'       => 'BOOL',
                'default'    => false
            ],
            'plan'  => [
                'type'       => 'ENUM("standard", "premium")',
                'null'       => true
            ],
            'year' => [
                'type' => 'INT',
                'constraint' => 4,
                'null' => true
            ],
            'year_condition' => [
                'type' => 'ENUM("before", "after")',
                'null' => true
            ],
            'min_star_rating'    => [
                'type'       => 'INT',
                'constraint' => 1,
                'null'       => true
            ],
            'max_star_rating'    => [
                'type'       => 'INT',
                'constraint' => 1,
                'null'       => true
            ],
            'dismissed_at'     => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'created_at'     => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at'     => [
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
        $this->forge->addForeignKey('professor_id','professors','id');
        $this->forge->addForeignKey('exam_id','exams','id');
        $this->forge->addForeignKey('school_id','schools','id');
        $this->forge->createTable('requests');
    }

    public function down()
    {
        $this->forge->dropTable('requests');
    }
}
