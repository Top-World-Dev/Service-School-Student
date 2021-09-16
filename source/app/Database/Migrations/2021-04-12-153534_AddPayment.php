<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPayment extends Migration
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
            'match_id'          => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
            ],
            'email'       => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
            ],
            'payment_option'      => [
                'type'  => 'VARCHAR',
                'constraint' => '30',
            ],
            'pp_batch_id'      => [
                'type'  => 'VARCHAR',
                'constraint' => '30',
                'null'       => true
            ],
            'pp_item_id'      => [
                'type'  => 'VARCHAR',
                'constraint' => '30',
                'null'       => true
            ],
            'amount'       => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
            ],
            'transfer_id'      => [
                'type'  => 'INT',
                'constraint' => '30',
                'null'       => true
            ],
            'status'      => [
                'type'  => 'VARCHAR',
                'constraint' => '30',
            ],
            'type'      => [
                'type'  => 'VARCHAR',
                'constraint' => '20',
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
        $this->forge->addForeignKey('match_id','matches','id');
        $this->forge->createTable('payments');
    }

    public function down()
    {
        $this->forge->dropTable('payments');
    }
}
