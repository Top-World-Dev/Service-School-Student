<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProfessor extends Migration
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
            'first_name'       => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'last_name'       => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'www_url'       => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'email'       => [
                'type'       => 'VARCHAR',
                'constraint' => '64',
            ],
            'verified'       => [
                'type'       => 'BOOL',
                'default'    => false,
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
        $this->forge->createTable('professors');
    }

    public function down()
    {
        $this->forge->dropTable('professors');
    }
}
