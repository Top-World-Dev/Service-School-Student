<?php

namespace App\Filters;

use App\Models\Role;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Exception;

class VerifiedEmailFilter implements FilterInterface
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
            if ($authUser) {
                if ($authUser['role_id'] == Role::STUDENT) {
                    if (!$authUser['verified']) {
                        return Services::response()
                            ->setJSON(['error' => 'email is not verified yet'])
                            ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
                    } else {
                        return $request;
                    }
                } else {
                    return $request;
                }
            } else {
                return Services::response()
                    ->setJSON(['error' => $e->getMessage()])
                    ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
            }
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
