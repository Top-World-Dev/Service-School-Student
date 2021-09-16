<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUser extends Migration
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
            'email'          => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'unique'     => true,
            ],
            'first_name'     => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'last_name'      => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'password'       => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'role_id'        => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
            ],
            'school_id'      => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true
            ],
            'verified'       => [
                'type'       => 'BOOL',
                'default'    => false
            ],
            'suspended'       => [
                'type'       => 'BOOL',
                'default'    => false
            ],
            'email_verification_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '64',
                'null'       => true
            ],
            'can_upload'          => [
                'type'       => 'BOOL',
                'default'    => false,
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
        $this->forge->addForeignKey('role_id','roles','id');
        $this->forge->addForeignKey('school_id','schools','id');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
