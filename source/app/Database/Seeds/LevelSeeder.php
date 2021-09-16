<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LevelSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('levels');

        $data = [
            [ 'name' => 'College Year 1' ],
            [ 'name' => 'College Year 2' ],
            [ 'name' => 'College Year 3' ],
            [ 'name' => 'College Year 4' ],
            [ 'name' => 'Advanced Graduate' ],
            [ 'name' => 'More Advanced Graduate' ],
        ];

        $builder->insertBatch($data);
    }
}
