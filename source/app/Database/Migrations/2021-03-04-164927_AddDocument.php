<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDocument extends Migration
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
            'cloud_url'      => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'user_id'        => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
            ],
            'verified'       => [
                'type'       => 'BOOL',
                'default'    => false
            ],
            'scan_status'    => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
            ],
            'created_at'     => [
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
        $this->forge->addForeignKey('user_id','users','id');
        $this->forge->createTable('documents');
    }

    public function down()
    {
        $this->forge->dropTable('documents');
    }
}
