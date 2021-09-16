<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGroupUser extends Migration
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
            'group_id'                 => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
            ],
            'user_id'                 => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
            ],
            'grade_value'       => [
                'type'       => 'DECIMAL',
                'constraint' => '5,3',
                'null'       => true
            ],
            'date_proven'     => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true
            ],
            'identity_proven'     => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true
            ],
            'exam_proven'     => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true
            ],
            'verified'         => [
                'type'       => 'BOOL',
                'default'    => false
            ],
            'last_view_at'       => [
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
        $this->forge->addForeignKey('group_id','groups','id');
        $this->forge->addForeignKey('user_id','users','id');
        $this->forge->createTable('group_users');
    }

    public function down()
    {
        $this->forge->dropTable('group_users');
    }
}
