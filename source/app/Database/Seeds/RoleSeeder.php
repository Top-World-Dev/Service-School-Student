<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('roles');
        $data = [
            [ 'name' => 'Admin' ],
            [ 'name' => 'Student' ],
            [ 'name' => 'Reviewer' ],
        ];

        $builder->insertBatch($data);
    }
}
