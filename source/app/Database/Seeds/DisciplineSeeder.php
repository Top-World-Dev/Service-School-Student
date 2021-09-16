<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DisciplineSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('disciplines');

        $data = [
            [ 'name' => 'Aerospace Engineering' ],
            [ 'name' => 'Agricultural Engineering' ],
            [ 'name' => 'Architectural Engineering' ],
            [ 'name' => 'Biomedical Engineering' ],
            [ 'name' => 'Chemical Engineering' ],
            [ 'name' => 'Civil Engineering' ],
            [ 'name' => 'Computer Engineering' ],
            [ 'name' => 'Construction Engineering' ],
            [ 'name' => 'Electrical Engineering' ],
            [ 'name' => 'Electronics Engineering' ],
            [ 'name' => 'Environmental Engineering' ],
            [ 'name' => 'Geotechnical Engineering' ],
            [ 'name' => 'Industrial Engineering' ],
            [ 'name' => 'Manufacturing Engineering' ],
            [ 'name' => 'Marine Engineering' ],
            [ 'name' => 'Materials Engineering' ],
            [ 'name' => 'Mechanical Engineering' ],
            [ 'name' => 'Metallurgical Engineering' ],
            [ 'name' => 'Mining Engineering' ],
            [ 'name' => 'Network Engineering' ],
            [ 'name' => 'Nuclear Engineering' ],
            [ 'name' => 'Packaging Engineering' ],
            [ 'name' => 'Petroleum Engineering' ],
            [ 'name' => 'Process Engineering' ],
            [ 'name' => 'Project Engineering' ],
            [ 'name' => 'Quality Engineering' ],
            [ 'name' => 'Safety Engineering' ],
            [ 'name' => 'Sales Engineeringg' ],
            [ 'name' => 'Software Engineering' ],
            [ 'name' => 'Solar Engineering' ],
            [ 'name' => 'Structural Engineering' ],
            [ 'name' => 'Systems Engineering' ],
            [ 'name' => 'Other Discipline' ],
        ];

        $builder->insertBatch($data);
    }
}
