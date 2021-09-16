<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReviewerCompetency extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'       => true,
            ],
            'disciplines'     => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'levels'     => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'subjects'     => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
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
        $this->forge->addForeignKey('user_id', 'users', 'id');
        $this->forge->createTable('reviewer_competencies');
    }

    public function down()
    {
        $this->forge->dropTable('reviewer_competencies');
    }
}
