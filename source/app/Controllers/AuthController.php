<?php

namespace App\Controllers;

use App\Models\Role;
use App\Libraries\AsyncLibrary;
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\MailService;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Exception;
use ReflectionException;

class AuthController extends BaseController
{
    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var AsyncLibrary
     */
    protected $asyncLib;

    /**
     * The constructor takes all compulsory arguments.
     *
     */
    public function __construct()
    {
        $this->authService = new AuthService();
        $this->userService = new UserService();
        $this->mailService = new MailService();
        $this->asyncLib = new AsyncLibrary();
    }

    /**
     * Register a new user
     * @return Response
     * @throws ReflectionException
     */
    public function register()
    {
        $rules = [
            'firstName' => 'required',
            'lastName' => 'required',
            'schoolId' => 'required',
            'email' => 'required|min_length[6]|max_length[50]|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]|max_length[255]',
        ];

        $input = $this->getRequestInput($this->request);
        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse(
                    $this->validator->getErrors(),
                    ResponseInterface::HTTP_BAD_REQUEST
                );
        }

        $email_verification_code = Uuid::uuid4();

        $this->userService->createUser(
            $input['firstName'],
            $input['lastName'],
            $input['email'],
            $input['password'],
            Role::STUDENT,
            $input['schoolId'],
            $email_verification_code
        );

        // $this->mailService->sendVerifyEmail($input['email'], $email_verification_code);
        $param = array(
            'to' => $input['email'],
            'verification_code' => $email_verification_code
        );
        $this->asyncLib->do_in_background(self::class, 'sendVerifyEmail', $param);

        return $this->getJWTForUser(
                $input['email'],
                ResponseInterface::HTTP_CREATED
            );
    }

    /**
     * Send an email verification code
     *
     * @param string $to
     * @param string $verification_code
     */
    public function sendVerifyEmail(string $to, string $verification_code)
    {
        $this->mailService->sendVerifyEmail($to, $verification_code);
    }

    /**
     * Authenticate Existing User
     * @return Response
     */
    public function login()
    {
        $rules = [
            'email' => 'required',
            'password' => 'required'
        ];

        $input = $this->getRequestInput($this->request);

        if (!$this->validateRequest($input, $rules)) {
            return $this
                ->getResponse(
                    $this->validator->getErrors(),
                    ResponseInterface::HTTP_BAD_REQUEST
                );
        }

        $user = $this->userService->findUserByEmailAddress($input['email']);

        if (empty($user)) {
            return $this->getResponse([
                'message' => 'Not found your account',
            ], ResponseInterface::HTTP_NOT_FOUND);
        } else if ($user->suspended) {
            return $this->getResponse([
                'message' => 'Your account is suspended',
            ], ResponseInterface::HTTP_UNAUTHORIZED);
        } else {
            helper('jwt');
            if ($user->role_id == Role::REVIEWER && is_array(json_decode($user->password))) {
                if(password_verify($input['password'], json_decode($user->password)[0])) {
                    return $this->getResponse([
                        'message' => 'User authenticated successfully',
                        'user' => $user->getFields(),
                        'access_token' => getSignedJWTForUser($input['email'])
                    ]);
                }
            }

            if (password_verify($input['password'], $user->password)) {
                return $this->getResponse([
                    'message' => 'User authenticated successfully',
                    'user' => $user->getFields(),
                    'access_token' => getSignedJWTForUser($input['email'])
                ]);
            }
        }

        return $this->getResponse([
            'message' => 'Invalid login credentials provided',
        ], ResponseInterface::HTTP_UNAUTHORIZED);
    }

    /**
     * Get authenticated user
     * @return Response
     */
    public function me()
    {
        return $this->getResponse([
                'user' => $this->user,
                'access_token' => getSignedJWTForUser($this->user['email'])
            ]);
    }

    /**
     * Send email for reset password.
     * @return Response
     */
    public function forgotPassword()
    {
        $rules = [
            'email' => 'required|valid_email',
        ];
        $input = $this->getRequestInput($this->request);

        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse(
                    $this->validator->getErrors(),
                    ResponseInterface::HTTP_BAD_REQUEST
                );
        }

        $user = $this->userService->findUserByEmailAddress($input['email']);

        if (empty($user)) {
            return $this->getResponse(
                    ['message' => 'We could not find the email'],
                    ResponseInterface::HTTP_BAD_REQUEST
                );
        }

        $token = $this->authService->createPasswordResetToken($input['email']);
        $this->mailService->sendPassowrdResetLink($input['email'], $token);
        return $this->getResponse([
                'message' => 'We successfully sent an password email link'
            ]);
    }

    /**
     * Check reset password token.
     * @return Response
     */
    public function checkResetPasswordToken()
    {
        $input = $this->getRequestInput($this->request);
        $response = $this->authService->checkPasswordResetToken($input['token']);
        if (!$response['success']) {
            return $this->getResponse(
                ['message' => $response['message']],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }
        return $this->getResponse([
                'success' => true
            ]);
    }

    /**
     * Reset password.
     * @return Response
     */
    public function resetPassword()
    {
        $input = $this->getRequestInput($this->request);
        $status = $this->authService->resetPassword($input['token'], $input['password']);
        return $this->getResponse([
                'status' => $status
            ]);
    }

    private function getJWTForUser(string $emailAddress, int $responseCode = ResponseInterface::HTTP_OK)
    {
        try {
            $user = $this->userService->findUserByEmailAddress($emailAddress);
            helper('jwt');

            return $this->getResponse([
                        'message' => 'User authenticated successfully',
                        'user' => $user->getFields(),
                        'access_token' => getSignedJWTForUser($emailAddress)
                    ]);
        } catch (Exception $exception) {
            return $this->getResponse(
                        ['error' => $exception->getMessage()],
                        $responseCode
                    );
        }
    }
}