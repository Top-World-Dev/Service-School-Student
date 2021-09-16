<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('RoleSeeder');
        $this->call('UserSeeder');
        $this->call('DisciplineSeeder');
        $this->call('SubjectSeeder');
        $this->call('LevelSeeder');
        $this->call('ProfessorSeeder');
        $this->call('SettingSeeder');
        $this->call('ExamSeeder');
        $this->call('RequestSeeder');
    }
}
