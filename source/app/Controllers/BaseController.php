<?php

namespace App\Controllers;

use App\CustomAR\Record;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Config\Services;
use Cycle\ORM;
use Psr\Log\LoggerInterface;
use Spiral\Database;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */

class BaseController extends Controller
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Cycle ORM
     *
     * @var ORM\ORM
     */
    protected $orm;

    /**
     * Current authenticated user
     *
     * @var \App\Models\User
     */
    protected $user;

    /**
     * Constructor.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param LoggerInterface   $logger
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        //--------------------------------------------------------------------
        // Preload any models, libraries, etc, here.
        //--------------------------------------------------------------------
        // E.g.: $this->session = \Config\Services::session();

        // Initiate ORM service
        $config = new \Config\CycleORM();
        $dbal = new Database\DatabaseManager($config->getDBConfig());

        $this->orm = new ORM\ORM(new ORM\Factory($dbal));

        $schema = $config->loadSchema($this->orm);
        $this->orm = $this->orm->withSchema(new ORM\Schema($schema));
        Record::setORM($this->orm);

        // Get current user
        $session = Services::session();
        $this->user = $session->get('auth');
    }

    public function getResponse(array $responseBody, int $code = ResponseInterface::HTTP_OK)
    {
        return $this->response
            ->setStatusCode($code)
            ->setJSON($responseBody);
    }

    public function getRequestInput(IncomingRequest $request){
        $input = $request->getPost();
        if (empty($input)) {
            //convert request body to associative array
            $input = json_decode($request->getBody(), true);
        }
        return $input;
    }

    public function validateRequest($input, array $rules, array $messages =[]){
        $this->validator = Services::Validation()->setRules($rules);
        // If you replace the $rules array with the name of the group
        if (is_string($rules)) {
            $validation = config('Validation');

            // If the rule wasn't found in the \Config\Validation, we
            // should throw an exception so the developer can find it.
            if (!isset($validation->$rules)) {
                throw ValidationException::forRuleNotFound($rules);
            }

            // If no error message is defined, use the error message in the Config\Validation file
            if (!$messages) {
                $errorName = $rules . '_errors';
                $messages = $validation->$errorName ?? [];
            }

            $rules = $validation->$rules;
        }
        return $this->validator->setRules($rules, $messages)->run($input);
    }
}
