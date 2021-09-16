<?php

namespace App\Controllers;

use App\Libraries\StripeLibrary;
use App\Services\ExamService;
use App\Services\RequestService;
use App\Services\ProfessorService;
use App\Services\MatchService;
use CodeIgniter\HTTP\ResponseInterface;

class RequestController extends BaseController
{
    /**
     * @var StripeLibrary
     */
    protected $stripeLib;

    /**
     * @var ExamService
     */
    protected $examService;

    /**
     * @var RequestService
     */
    protected $requestService;

    /**
     * @var ProfessorService
     */
    protected $profService;

    /**
     * @var MatchService
     */
    protected $matchService;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->stripeLib = new StripeLibrary();
        $this->examService = new ExamService();
        $this->requestService = new RequestService();
        $this->profService = new ProfessorService();
        $this->matchService = new MatchService();
    }

    /**
     * Get all requests.
     *
     * @return mixed
     */
    public function index()
    {
        $withRelations = ['discipline', 'level', 'subject', 'student', 'school'];
        $requests = $this->requestService->getAll($withRelations);
        return $this->getResponse($requests);
    }

    /**
     * Get a request by Id for student.
     *
     * @return mixed
     */
    public function getById(int $id)
    {
        $with = ['discipline', 'level', 'subject', 'student', 'school'];
        $request = $this->requestService->getById($id, $with);
        $requestData = $request->getFields();
        $requestData['matches'] = $request->getExams();
        return $this->getResponse(['request' => $requestData]);
    }

    /**
     * Get requests by user.
     *
     * @return mixed
     */
    public function getByUser()
    {
        $withRelations = ['discipline', 'level', 'subject'];
        $requests = $this->requestService->getByUser($this->user['id'], $withRelations);
        return $this->getResponse($requests);
    }

    /**
     * Get all requests by user.
     *
     * @return mixed
     */
    public function create()
    {
        $rules = [
            'discipline_id' => 'required|numeric',
            'level_id' => 'required|numeric',
            'subject_id' => 'required|numeric',
        ];

        $input = $this->getRequestInput($this->request);

        if (!$this->validateRequest($input, $rules)) {
            return $this
                ->getResponse(
                    $this->validator->getErrors(),
                    ResponseInterface::HTTP_BAD_REQUEST
                );
        }

        // Find a requested professor
        $professor = $this->profService->findOneOrNullByEmail(
            $input['prof_email']
        );

        // Create new request
        $request = $this->requestService->create(
            $this->user['id'],
            $input['discipline_id'],
            $input['level_id'],
            $input['subject_id'],
            $input['exam_date'],
            $input['exam_number'],
            $input['duration'],
            $input['semester'],
            $input['delay'],
            $input['course_num'],
            $input['other_school'],
            $input['other_semester'],
            $input['other_professor'],
            $this->user['school_id'],
            empty($professor) ? null : $professor->id,
            $input['plan'] == 'mixed' ? null: $input['plan'],
            isset($input['year']) ? $input['year'] : null,
        );

        return $this->getResponse([
                        'message' => 'Request sent successfully',
                    ]);
    }

    /**
     * Dismiss a request
     *
     * @return mixed
     */
    public function dismiss(int $requestId)
    {
        $request = $this->requestService->dismiss($requestId);
        return $this->getResponse([
                        'message' => 'Request dismissed successfully',
                        'status' => true,
                    ]);
    }

    /**
     * Delete a request
     *
     * @return mixed
     */
    public function delete(int $requestId)
    {
        $this->matchService->deleteByRequestId($requestId);
        $request = $this->requestService->delete($requestId);
        return $this->getResponse([
                        'message' => 'Request deleted successfully',
                        'status' => true,
                    ]);
    }

    /**
     * Purchase an exam
     *
     * @return mixed
     */
    public function purchase(int $request_id)
    {
        $rules = [
            'email' => 'required',
            'exam_id' => 'required|numeric',
        ];

        $input = $this->getRequestInput($this->request);

        if (!$this->validateRequest($input, $rules)) {
            return $this
                ->getResponse(
                    $this->validator->getErrors(),
                    ResponseInterface::HTTP_BAD_REQUEST
                );
        }

        $exam = $this->examService->getExamById($input['exam_id'], []);
        $examData = $exam->getFields();

        $plan = 'single';
        if ($examData['plan'] == 'premium') {
            $plan = 'premium';
        } else if($examData['group_id']) {
            $plan = 'group';
        }

        $session = $this->stripeLib->createSession($input['email'], $plan);
        $this->matchService->savePaymentId($request_id, $input['exam_id'], $session['id']);
        return $this->getResponse(['status' => $session]);
    }

    /**
     * Process payment result
     *
     * @return mixed
     */
    public function processStripeResult()
    {
        $input = $this->getRequestInput($this->request);

        if ($input['type'] === 'checkout.session.completed') {
            $this->matchService->paidMatch($input['data']['object']['id']);
        }
    }
}
