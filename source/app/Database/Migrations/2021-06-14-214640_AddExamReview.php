<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExamReview extends Migration
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
            'status'  => [
                'type'       => 'ENUM("sme-pending", "sme-unchanged", "sme-modified", "sme-rejected")',
                'null'       => true
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
        $this->forge->createTable('exam_reviews');
    }

    public function down()
    {
        $this->forge->dropTable('exam_reviews');
    }
}
