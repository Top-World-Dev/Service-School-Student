<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExamRating extends Migration
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
			'user_id'       => [
				'type'       => 'INT',
				'constraint' => 5,
				'unsigned'       => true,
			],
			'exam_id'       => [
				'type'       => 'INT',
				'constraint' => 5,
				'unsigned'       => true,
			],
			'stars'  => [
				'type'       => 'INT',
				'constraint'       => 1,
			],
			'review_body'  => [
				'type'       => 'VARCHAR',
				'constraint'       => 150,
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
		$this->forge->addForeignKey('user_id', 'users', 'id');
		$this->forge->addForeignKey('exam_id', 'exams', 'id');
		$this->forge->createTable('exam_ratings');
	}

	public function down()
	{
		$this->forge->dropTable('exam_ratings');
	}
}
