<?php

namespace App\Controllers;

use App\Services\CountryService;
use CodeIgniter\HTTP\ResponseInterface;

class CountryController extends BaseController
{
    /**
     * @var CountryService
     */
    protected $countryService;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->countryService = new CountryService();
    }

    /**
     * Get all countries.
     *
     * @return mixed
     */
    public function index()
    {
        $countries = $this->countryService->getAll();
        return $this->getResponse($countries);
    }
}
