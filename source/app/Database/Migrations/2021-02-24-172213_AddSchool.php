<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSchool extends Migration
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
            'school_name'    => [
                'type'       => 'VARCHAR',
                'constraint' => '180',
            ],
            'school_abbreviation'     => [
                'type'       => 'VARCHAR',
                'constraint' => '90',
                'null'       => true
            ],
            'school_url'     => [
                'type'       => 'VARCHAR',
                'constraint' => '300',
                'null'       => true
            ],
            'school_city'    => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true
            ],
            'school_state'   => [
                'type'       => 'VARCHAR',
                'constraint' => '90',
                'null'       => true
            ],
            'country_id'     => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true
            ],
            'offer_email'    => [
                'type'       => 'BOOL',
                'default'    => true,
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
        $this->forge->addForeignKey('country_id','countries','id');
        $this->forge->createTable('schools');
    }

    public function down()
    {
        $this->forge->dropTable('schools');
    }
}
