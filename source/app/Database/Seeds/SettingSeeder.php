<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('settings');

        $data = [
            [
                'name' => 'payment_methods',
                'value' => json_encode([
                    'PayPal' => true,
                    'TransferWise' => true,
                    'Payoneer' => false,
                    'Cashapp' => false
                ]),
                'type' => 'array'
            ],
        ];

        $builder->insertBatch($data);
    }
}
