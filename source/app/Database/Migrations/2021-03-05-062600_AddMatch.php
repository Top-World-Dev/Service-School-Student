<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMatch extends Migration
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
            'request_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'       => true,
            ],
            'exam_id'       => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'       => true,
            ],
            'payment_id'       => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true
            ],
            'status'      => [
                'type'  => 'ENUM("pending","selected","paid")',
                'default' => 'pending'
            ],
            'paid'      => [
                'type'  => 'BOOL',
                'default' => false
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
        $this->forge->addForeignKey('request_id','requests','id');
        $this->forge->addForeignKey('exam_id','exams','id');
        $this->forge->createTable('matches');
    }

    public function down()
    {
        $this->forge->dropTable('matches');
    }
}
