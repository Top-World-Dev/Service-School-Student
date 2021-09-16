<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExamContent extends Migration
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
            'reviewer_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'       => true,
            ],
            'exam_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'       => true,
            ],
            'question'            => [
                'type'           => 'TEXT',
                'constraint'     => 500,
            ],
            'solution'            => [
                'type'           => 'TEXT',
                'constraint'     => 2000,
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
        $this->forge->addForeignKey('reviewer_id', 'users', 'id');
        $this->forge->addForeignKey('exam_id', 'exams', 'id');
        $this->forge->createTable('exam_contents');
    }

    public function down()
    {
        $this->forge->dropTable('exam_contents');
    }
}
