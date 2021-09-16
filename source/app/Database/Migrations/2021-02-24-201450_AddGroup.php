<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGroup extends Migration
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
            'name'     => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'status'     => [
                'type'       => 'ENUM("initialized", "in-progress", "pending-admin-review", "active", "system-dismissed")',
            ],
            'owner_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
            ],
            'quality'     => [
                'type'       => 'ENUM("poor", "uneven", "good")',
                'null'       => true,
            ],
            'rejected_at'       => [
                'type'       => 'DATETIME',
                'null'       => true,
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
        $this->forge->addForeignKey('owner_id','users','id');
        $this->forge->createTable('groups');
    }

    public function down()
    {
        $this->forge->dropTable('groups');
    }
}
