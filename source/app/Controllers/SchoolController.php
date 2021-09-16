<?php

namespace App\Controllers;

use App\Services\SchoolService;
use CodeIgniter\HTTP\ResponseInterface;

class SchoolController extends BaseController
{
    /**
     * @var SchoolService
     */
    protected $schoolService;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->schoolService = new SchoolService();
    }

    /**
     * Get schools by country.
     *
     * @return mixed
     */
    public function index()
    {
        $countryId = $this->request->getVar('country_id');
        if ($countryId) {
            $schools = $this->schoolService->getByCountry($countryId);
            return $this->getResponse($schools);
        }
        $schools = $this->schoolService->getAll();
        return $this->getResponse($schools);
    }

    /**
     * Add new school abbreviation
     *
     * @param int $id - school Id
     * @return mixed
     */
    public function addAbbreviation(int $id)
    {
        $rules = [
            'abbreviation' => 'required'
        ];

        $input = $this->getRequestInput($this->request);
        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse(
                $this->validator->getErrors(),
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        $school = $this->schoolService->getById($id);
        $abbreviations_arr = json_decode($school->school_abbreviation);
        array_push($abbreviations_arr, $input['abbreviation']);
        $school->school_abbreviation = json_encode($abbreviations_arr);
        $school->save();
        return $this->getResponse(['status' => true]);
    }

    /**
     * remove a school abbreviation
     *
     * @param int $id - school Id
     * @return mixed
     */
    public function removeAbbreviation(int $id)
    {
        $rules = [
            'abbreviation' => 'required'
        ];

        $input = $this->getRequestInput($this->request);
        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse(
                $this->validator->getErrors(),
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        $school = $this->schoolService->getById($id);
        $abbreviations_arr = json_decode($school->school_abbreviation);
        $key = array_search($input['abbreviation'], $abbreviations_arr); // This function may return Boolean false, but may also return a non-Boolean value which evaluates to false.
        if ($key !== false) { // we must use the === or !== operators for testing the return value of the function.
            unset($abbreviations_arr[$key]);
        } else {
            $this->validator->setError('abbreviation', 'The specified school abbreviation to remove was not found!');
            return $this->getResponse(
                $this->validator->getErrors(),
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }
        $school->school_abbreviation = json_encode($abbreviations_arr);
        $school->save();
        return $this->getResponse(['status' => true]);
    }
}
