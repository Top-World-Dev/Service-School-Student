<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUpload extends Migration
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
            'type'      => [
                'type'  => 'ENUM("u_act","g_act","u_mock","g_mock")',
            ],
            'exam_id'        => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
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
        $this->forge->addForeignKey('exam_id','exams','id');
        $this->forge->createTable('uploads');
    }

    public function down()
    {
        $this->forge->dropTable('uploads');
    }
}
