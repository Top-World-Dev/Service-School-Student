<?php

namespace App\Filters;

use App\Models\Role;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Exception;

class AdminFilter implements FilterInterface
{
    use ResponseTrait;

    public function before(RequestInterface $request, $arguments = null)
    {
        $authenticationHeader = $request->getServer('HTTP_AUTHORIZATION');

        try {
            helper('jwt');
            $encodedToken = getJWTFromRequest($authenticationHeader);
            validateJWTFromRequest($encodedToken);

            // Get current user
            $session = Services::session();
            $authUser = $session->get('auth');
            if ($authUser && $authUser['role_id'] == Role::ADMIN) {
                return $request;
            }
            return Services::response()
                ->setJSON(['error' => "You don't have permission to this operation"])
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return Services::response()
                ->setJSON(['error' => $e->getMessage()])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}