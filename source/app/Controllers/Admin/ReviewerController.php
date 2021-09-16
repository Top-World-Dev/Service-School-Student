<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ReviewerCompetency;
use App\Models\Role;
use App\Models\User;
use App\Libraries\AsyncLibrary;
use App\Services\ExamReviewService;
use App\Services\ExamService;
use App\Services\UserService;
use App\Services\SchoolService;
use App\Services\ReviewerCompetencyService;
use App\Services\ProfessorService;
use App\Services\MailService;
use CodeIgniter\HTTP\ResponseInterface;
use Spiral\Database\Injection\Parameter;
use Ramsey\Uuid\Uuid;

class ReviewerController extends BaseController
{
    /**
     * @var ExamReviewService
     */
    protected $examReviewService;

    /**
     * @var ExamService
     */
    protected $examService;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var ReviewerCompetencyService
     */
    protected $reviewerCompetencyService;

    /**
     * @var ProfessorService
     */
    protected $professorService;

    /**
     * @var SchoolService
     */
    protected $schoolService;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var AsyncLibrary
     */
    protected $asyncLib;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->examReviewService = new ExamReviewService();
        $this->examService = new ExamService();
        $this->userService = new UserService();
        $this->reviewerCompetencyService = new ReviewerCompetencyService();
        $this->professorService = new ProfessorService();
        $this->schoolService = new SchoolService();
        $this->mailService = new MailService();
        $this->asyncLib = new AsyncLibrary();
    }

    /**
     * Create new reviewer
     *
     * @return mixed
     */
    public function create()
    {
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|min_length[6]|max_length[50]|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]|max_length[255]',
            'school_id' => 'required',
            'disciplines' => 'required',
            'levels' => 'required',
            'subjects' => 'required',
        ];

        $input = $this->getRequestInput($this->request);
        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse(
                $this->validator->getErrors(),
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        helper('utils_helper');

        // Check if the provided email address has the matched school domain.
        $school = $this->schoolService->getById($input['school_id']);
        if (!empty($school)) {
            $schoolDomain = getDomainFromEmail($input['email']);
            if (!in_array($schoolDomain, json_decode($school->school_abbreviation))) {
                return $this->getResponse(
                    ['message' => 'School domain does not match'],
                    ResponseInterface::HTTP_BAD_REQUEST
                );
            }
        }

        $email_verification_code = Uuid::uuid4();

        // Register new reviewer
        $user = $this->userService->createUser(
            $input['first_name'],
            $input['last_name'],
            $input['email'],
            $input['password'],
            Role::REVIEWER,
            $input['school_id'],
            $email_verification_code,
            true
        );

        // Send verification email.
        $param = array(
            'to' => $input['email'],
            'verification_code' => $email_verification_code
        );
        $this->asyncLib->do_in_background(self::class, 'sendVerifyEmail', $param);

        // Add reviewer's competency
        $competency = new ReviewerCompetency();
        $competency->user_id = $user->id;
        $competency->disciplines = json_encode($input['disciplines']);
        $competency->levels = json_encode($input['levels']);
        $competency->subjects = json_encode($input['subjects']);
        $competency->save();

        return $this->getResponse([
            'message' => 'Reviewer added successfully',
        ]);
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
     * Update a reviewer
     *
     * @param int $id
     * @return mixed
     */
    public function updateReviewer(int $id)
    {
        $rules = [
            'disciplines' => 'required',
            'levels' => 'required',
            'subjects' => 'required',
        ];

        $input = $this->getRequestInput($this->request);
        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse(
                $this->validator->getErrors(),
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        $reviewer = $this->userService->getUserById($id);

        if (empty($reviewer)) {
            return $this->getResponse(
                ['message' => 'reviewer does not exist'],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        // Update a reviewer
        // $reviewer = $this->userService->update($id, $input['first_name'], $input['last_name'], $input['email'], $input['password']);

        // Add reviewer's competency
        $competency = ReviewerCompetency::findOne(['user_id' => $id]);
        $competency->disciplines = json_encode($input['disciplines']);
        $competency->levels = json_encode($input['levels']);
        $competency->subjects = json_encode($input['subjects']);
        $competency->save();

        return $this->getResponse([
            'message' => 'Reviewer is updated successfully',
        ]);
    }

    /**
     * Get reviewers by competency
     *
     * @return mixed
     */
    public function getByCompetency()
    {
        $rules = [
            'discipline_id' => 'required|numeric',
            'level_id' => 'required|numeric',
            'subject_id' => 'required|numeric',
        ];

        $input = $this->getRequestInput($this->request);
        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse(
                $this->validator->getErrors(),
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        $competencies = $this->reviewerCompetencyService->getAllByCompetency(
            $input['discipline_id'],
            $input['level_id'],
            $input['subject_id'],
        );

        $reviewerIds = array_map(function ($competency) {
            return $competency['user_id'];
        }, $competencies);

        $reviewers = array();
        if ($reviewerIds) {
            $users = User::findAll(['id' => ['in' => new Parameter($reviewerIds)]]);
            foreach ($users as $user) {
                array_push($reviewers, $user->getReviewerFields());
            }
        }

        return $this->getResponse([
            'reviewers' => $reviewers,
        ]);
    }

    /**
     * Request review from reviewers
     *
     * @return mixed
     */
    public function requestReview()
    {
        $rules = [
            'exam_id' => 'required|numeric',
            'reviewers_ids' => 'required',
        ];

        $input = $this->getRequestInput($this->request);
        if (!$this->validateRequest($input, $rules)) {
            return $this->getResponse(
                $this->validator->getErrors(),
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        // Validate exam's existance.
        $exam = $this->examService->getExamById($input['exam_id']);
        if (empty($exam)) {
            return $this->getResponse(
                ['message' => 'exam does not exist'],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        // Validate reviewer's existance.
        foreach ($input['reviewers_ids'] as $reviewer_id) {
            $user = $this->userService->getUserById($reviewer_id);
            if (empty($user)) {
                return $this->getResponse(
                    ['message' => 'reviewer does not exist'],
                    ResponseInterface::HTTP_BAD_REQUEST
                );
            }
        }


        // Create request review records
        foreach ($input['reviewers_ids'] as $reviewer_id) {
            $this->examReviewService->create($input['exam_id'], $reviewer_id);
        }

        return $this->getResponse([
            'request' => 'review is requested successfully',
        ]);
    }

    /**
     * Get all reviewers.
     *
     * @return mixed
     */
    public function getReviewers()
    {
        $reviewers = User::findAll(['role_id' => Role::REVIEWER]);

        $iterator = array();
        foreach ($reviewers as $reviewer) {
            array_push($iterator, $reviewer->getReviewerFields());
        }
        return $this->getResponse($iterator);
    }

    /**
     * Get a reviewer by Id.
     *
     * @param int $id
     * @return mixed
     */
    public function getById(int $id)
    {
        $reviewer = $this->userService->getUserById($id, ['school', 'school.country']);
        if (empty($reviewer)) {
            return $this->getResponse(
                ['message' => 'reviewer does not exist'],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        } else {
            if ($reviewer->role_id !== Role::REVIEWER) {
                return $this->getResponse(
                    ['message' => 'reviewer does not exist'],
                    ResponseInterface::HTTP_BAD_REQUEST
                );
            }
            $reviewerData = $reviewer->getReviewerFields();
            return $this->getResponse(['reviewer' => $reviewerData]);
        }
    }
}
