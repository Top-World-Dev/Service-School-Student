<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProfessorSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('professors');
        $data = [
            [
                'first_name' => 'David',
                'last_name' => 'Bowie',
                'www_url' => 'https://www.uaa.alaska.edu/academics/college-of-arts-and-sciences/departments/english/faculty/bowie.cshtml',
                'email' => 'david.bowie@alaska.edu',
            ],
        ];

        $builder->insertBatch($data);
    }
}
