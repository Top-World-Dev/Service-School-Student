<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGroupExam extends Migration
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
            'group_user_id'                 => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
            ],
            'question'           => [
                'type'           => 'TEXT',
                'constraint'     => 500,
            ],
            'solution'           => [
                'type'           => 'TEXT',
                'constraint'     => 2000,
            ],
            'is_final'           => [
                'type'           => 'BOOL',
                'default'        => false
            ],
            'created_at'         => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'updated_at'         => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'deleted_at'         => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('group_user_id','group_users','id');
        $this->forge->createTable('group_exams');
    }

    public function down()
    {
        $this->forge->dropTable('group_exams');
    }
}
