<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('users');
        $data = [
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@xamlinx.com',
                'password' => password_hash('password', PASSWORD_BCRYPT),
                'role_id' => 1,
                'school_id' => 1,
                'verified' => true,
                'can_upload' => true,
            ],
            [
                'first_name' => 'Jack',
                'last_name' => 'Smith',
                'email' => 'jack@alaska.edu',
                'password' => password_hash('password', PASSWORD_BCRYPT),
                'role_id' => 2,
                'school_id' => 1,
                'verified' => true,
                'can_upload' => true,
            ],
            [
                'first_name' => 'Eli',
                'last_name' => 'Bernan',
                'email' => 'eli@alaska.edu',
                'password' => password_hash('password', PASSWORD_BCRYPT),
                'role_id' => 2,
                'school_id' => 1,
                'verified' => true,
                'can_upload' => true,
            ],
            [
                'first_name' => 'Mark',
                'last_name' => 'Stu',
                'email' => 'mark@alaska.edu',
                'password' => password_hash('password', PASSWORD_BCRYPT),
                'role_id' => 2,
                'school_id' => 1,
                'verified' => true,
                'can_upload' => true,
            ],
            [
                'first_name' => 'Ben',
                'last_name' => 'Smith',
                'email' => 'ben@alaska.edu',
                'password' => password_hash('password', PASSWORD_BCRYPT),
                'role_id' => 2,
                'school_id' => 1,
                'verified' => true,
                'can_upload' => true,
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john@alaska.edu',
                'password' => password_hash('password', PASSWORD_BCRYPT),
                'role_id' => 2,
                'school_id' => 1,
                'verified' => true,
                'can_upload' => true,
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Bowie',
                'email' => 'david@alaska.edu',
                'password' => password_hash('password', PASSWORD_BCRYPT),
                'role_id' => 3,
                'school_id' => 1,
                'verified' => true,
                'can_upload' => true,
            ],
        ];

        $builder->insertBatch($data);
    }
}
