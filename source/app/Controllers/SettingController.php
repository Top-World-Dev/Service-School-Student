<?php

namespace App\Controllers;

use App\Services\SettingService;

class SettingController extends BaseController
{
    /**
     * @var SettingService
     */
    protected $settingService;

    /**
     * The constructor
     */
    public function __construct()
    {
        $this->settingService = new SettingService();
    }

    /**
     * Get all settings
     */
    public function index()
    {
        $settings = $this->settingService->getAll();
        return $this->getResponse($settings);
    }

    /**
     * Get available payment methods
     */
    public function getAvailablePaymentMethods()
    {
        $setting = $this->settingService->getOneByName('payment_methods');
        return $this->getResponse($setting);
    }

    /**
     * Update settings
     */
    public function update()
    {
        $input = $this->getRequestInput($this->request);

        switch ($input['name']) {
            case 'payment_methods':
                $this->settingService->updatePaymentMethods($input['key'], $input['value']);
                break;
            
            default:
                break;
        }

        return $this->getResponse(['status' => true]);
    }
}
