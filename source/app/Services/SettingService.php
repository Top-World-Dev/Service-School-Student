<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

/**
 * Setting Service
 */
class SettingService
{
    /**
     * Get all settings.
     *
     * @return array
     */
    public function getAll(): array
    {
        $settings = Setting::findAll([]);
        $iterator = array();
        foreach ($settings as $setting) {
            array_push($iterator, $setting->getFields());
        }
        return $iterator;
    }

    /**
     * Get a setting by name.
     *
     * @param string $name
     * @return array
     */
    public function getOneByName(string $name): array
    {
        $setting = Setting::findOne(['name' => $name], []);
        return $setting->getFields();
    }

    /**
     * Update payment methods
     *
     * @param string $key
     * @param bool $value
     */
    public function updatePaymentMethods(string $key, bool $value)
    {
        $setting = Setting::findOne(['name' => 'payment_methods'], []);
        $data = $setting->getFields();
        $payment_methods = $data['value'];
        $payment_methods[$key] = $value;
        $setting->value = json_encode($payment_methods);
        $setting->save();
    }
}