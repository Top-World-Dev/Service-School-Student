<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('subjects');

        $data = [
            [ 'name' => 'Medicine' ],
            [ 'name' => 'Physical Science & Mechanics' ],
            [ 'name' => 'Mathematics & Reasoning' ],
            [ 'name' => 'Research & Experimentation' ],
            [ 'name' => 'Reading & Law' ],
            [ 'name' => 'History' ],
        ];

        $builder->insertBatch($data);
    }
}
