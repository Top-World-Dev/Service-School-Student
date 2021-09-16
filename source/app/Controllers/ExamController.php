<?php

namespace App\Controllers;

use App\Libraries\AsyncLibrary;
use App\Libraries\CloudinaryLibrary;
use App\Models\Group;
use App\Services\DisciplineService;
use App\Services\ExamService;
use App\Services\GroupService;
use App\Services\LevelService;
use App\Services\SubjectService;
use App\Services\ProfessorService;
use App\Services\UploadService;
use App\Services\MailService;
use CodeIgniter\HTTP\ResponseInterface;
use Cloudinary\Transformation\Effect;
use Cloudinary\Transformation\Region;

class ExamController extends BaseController
{
    /**
     * @var ExamService
     */
    protected $examService;

    /**
     * @var GroupService
     */
    protected $groupService;

    /**
     * @var DisciplineService
     */
    protected $disciplineService;

    /**
     * @var LevelService
     */
    protected $levelService;

    /**
     * @var SubjectService
     */
    protected $subjectService;

    /**
     * @var ProfessorService
     */
    protected $professorService;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var AsyncLibrary
     */
    protected $asyncLib;

    /**
     * @var CloudinaryLibrary
     */
    protected $cloudLib;

    /**
     * @var string
     */
    protected $tempUploadPath = WRITEPATH . 'uploads';

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->examService = new ExamService();
        $this->groupService = new GroupService();
        $this->disciplineService = new DisciplineService();
        $this->levelService = new LevelService();
        $this->subjectService = new SubjectService();
        $this->professorService = new ProfessorService();
        $this->mailService = new MailService();
        $this->asyncLib = new AsyncLibrary();
        $this->cloudLib = new CloudinaryLibrary();
    }

    /**
     * Get all uploaded exams.
     *
     * @return mixed
     */
    public function index()
    {
        $withRelations = ['discipline', 'level', 'subject', 'student', 'school'];
        $exams = $this->examService->getAll($withRelations);
        return $this->getResponse($exams);
    }

    /**
     * Get all desciplines.
     *
     * @return mixed
     */
    public function getAllDesciplines()
    {
        $disciplines = $this->disciplineService->getAll();
        return $this->getResponse($disciplines);
    }

    /**
     * Get all levels.
     *
     * @return mixed
     */
    public function getAllLevels()
    {
        $levels = $this->levelService->getAll();
        return $this->getResponse($levels);
    }

    /**
     * Get all subjects.
     *
     * @return mixed
     */
    public function getAllSubjects()
    {
        $subjects = $this->subjectService->getAll();
        return $this->getResponse($subjects);
    }

    /**
     * Get a professor by email.
     *
     * @param string $email
     * @return mixed
     */
    public function getProfessorByEmail(string $email)
    {
        $professor = $this->professorService->findOneOrNullByEmail($email);
        if ($professor) {
            return $this->getResponse(['professor' => $professor->getFields()]);
        }
        return $this->getResponse(['professor' => null]);
    }

    /**
     * Get an group by name.
     *
     * @param string $name
     * @return mixed
     */
    public function getGroupByName(string $name)
    {
        $group = $this->groupService->getOneByName($name);
        if ($group) {
            return $this->getResponse(['group' => $group->getFields()]);
        }
        return $this->getResponse(['group' => null]);
    }

    /**
     * Get all uploaded exams.
     *
     * @return mixed
     */
    public function getExamsByUser()
    {
        $withRelations = ['discipline', 'level', 'subject', 'group'];
        $exams = $this->examService->getExamsByUser($this->user['id'], $withRelations);
        return $this->getResponse($exams);
    }

    /**
     * Get an exam by Id.
     *
     * @return mixed
     */
    public function getExamById(int $id)
    {
        $withRelations = ['discipline', 'level', 'subject', 'professor', 'student', 'school', 'group'];
        $exam = $this->examService->getExamById($id, $withRelations);
        $examData = $exam->getFields();
        if (!empty($examData['group'])) {
            $groupUser = $this->groupService->getGroupUser($examData['group']['id'], $this->user['id']);
            $groupUsers = $this->groupService->getGroupUsers($examData['group_id'], ['user']);
            $examData['group_files'] = $groupUser->getFields();
            $examData['qas'] = $this->groupService->getMemberQA($groupUser->id);
            $examData['users'] = $groupUsers;
        }
        $examData['uploads'] = $exam->getUploads();
        return $this->getResponse(['exam' => $examData]);
    }

    /**
     * Mark an upload exam as verified
     *
     * @return mixed
     */
    public function markAsVerfied()
    {
        $input = $this->getRequestInput($this->request);
        $this->examService->markAsVerified($input['id']);
        return $this->getResponse(['status' => true]);
    }

    /**
     * Send an email to a stuent to upload exams for requested demands.
     *
     * @return mixed
     */
    public function askToUpload()
    {
        $input = $this->getRequestInput($this->request);
        $exam = $this->examService->getExamById($input['exam_id'], ['student']);
        $this->mailService->sendForAskingToUpload($exam->student->email);
        return $this->getResponse(['status' => true]);
    }

    /**
     * Create a new exam
     *
     * @return mixed
     */
    public function create()
    {
        // Validate request parameters.
        $rules = [
            'mode' => 'required',
            'summary' => 'required',
            'grade_value' => 'required|decimal',
            'discipline_id' => 'required|numeric',
            'subject_id' => 'required|numeric',
            'level_id' => 'required|numeric',
            'exam_date' => 'required',
            'semester' => 'required',
            'prof_first_name' => 'required',
            'prof_last_name' => 'required',
            'prof_web' => 'required',
            'prof_email' => 'required'
        ];

        $input = array(
            'mode' => $this->request->getVar('mode') ? $this->request->getVar('mode') : 'single',
            'group_name' => $this->request->getVar('group_name'),
            'summary' => $this->request->getVar('summary'),
            'grade_value' => $this->request->getVar('grade_value') ? $this->request->getVar('grade_value') : null,
            'discipline_id' => $this->request->getVar('discipline_id'),
            'subject_id' => $this->request->getVar('subject_id'),
            'level_id' => $this->request->getVar('level_id'),
            'course_number' => $this->request->getVar('course_number'),
            'exam_number' => $this->request->getVar('exam_number'),
            'exam_date' => $this->request->getVar('exam_date'),
            'duration' => $this->request->getVar('duration'),
            'semester' => $this->request->getVar('semester'),
            'prof_first_name' => $this->request->getVar('prof_first_name'),
            'prof_last_name' => $this->request->getVar('prof_last_name'),
            'prof_web' => $this->request->getVar('prof_web'),
            'prof_email' => $this->request->getVar('prof_email'),
            'student_id' => $this->user['id'],
            'school_id' => $this->user['school_id'],
            'questions_and_solutions' => $this->request->getVar('questions_and_solutions'),
        );

        if ($input['mode'] == 'single' && !$this->validateRequest($input, $rules)) {
            return $this->getResponse(
                $this->validator->getErrors(),
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        $isNewGroup = false;
        if ($input['mode'] == 'group') {
            // Check and add a new member to a group
            if (!$this->groupService->checkCreateOrJoinAvailability($this->user['id'])) {
                return $this->getResponse(
                    [ 'message' => "You can't create or join to a group anymore, because you are already a member of a group in progress."],
                    ResponseInterface::HTTP_BAD_REQUEST
                );
            }
            $group = $this->groupService->getOneByName($input['group_name']);
            $groupId = null;
            if (empty($group)) {
                $group = $this->groupService->create($input['group_name'], $this->user['id']);
                $groupId = $group->id;
                $isNewGroup = true;
            } else {
                $groupId = $group->id;
                $groupuser = $this->groupService->findOneByGroupAndUser($groupId, $this->user['id']);
                if (!empty($groupuser)) {
                    return $this->getResponse(
                        [ 'message' => "You are already a member of this group."],
                        ResponseInterface::HTTP_BAD_REQUEST
                    );
                }
            }

            // Check 24hours after the group is created.
            $afterDay = $group->created_at->add((new \DateInterval('P1D')));
            if ($afterDay < (new \DateTime())) {
                return $this->getResponse(
                    [ 'message' => "You can't join because the group is locked"],
                    ResponseInterface::HTTP_BAD_REQUEST
                );
            }

            // Check member's count and add a new member to a group
            $groupUsers = $this->groupService->getGroupUsers($groupId);
            if (count($groupUsers) >= Group::MAX_GROUP_MEMBERS_COUNT) {
                return $this->getResponse(
                    [ 'message' => "The group is already full"],
                    ResponseInterface::HTTP_BAD_REQUEST
                );
            }
            $groupUser = $this->groupService->addUser($groupId, $this->user['id']);
            $groupUserId = $groupUser->id;

            // Update a group's status
            GroupService::updateGroupStatus($groupId);

            // Add questions & solutions
            $questions_and_solutions = json_decode($input['questions_and_solutions'], true);
            foreach ($questions_and_solutions as  $questions_and_solution) {
                $this->groupService->addQA($groupUserId, $questions_and_solution);
            }

            $input['group_id'] = $groupId;
        }

        helper('prof_validate_helper');

        if ($isNewGroup || $input['mode'] == 'single') {
            $professor = $this->professorService->findOneOrNullByEmail($input['prof_email']);

            if (empty($professor) && !validate_professor($input['prof_web'], $input['prof_email'], $input['prof_first_name'], $input['prof_last_name'])) {
                return $this->getResponse(
                    [ 'message' => "Couldn't authenticate your professor from the prof web address provided!"],
                    ResponseInterface::HTTP_BAD_REQUEST
                );
            }

            if (empty($professor)) {
                // Add new professor
                $professor = $this->professorService->create(
                    $input['prof_first_name'],
                    $input['prof_last_name'],
                    $input['prof_email'],
                    $input['prof_web'],
                    true
                );
            }

            $input['professor_id'] = $professor->id;
        }

        // Create new record for a exam.
        $exam = $this->examService->createExam($input);

        if ($input['mode'] == 'single') {
            // Upload images to cloudinary asyncronously.
            $ungradedActual = $this->request->getFile('ungraded_actual');
            $gradedActual = $this->request->getFile('graded_actual');
            $ungradedMock = $this->request->getFile('ungraded_mock');
            $gradedMock = $this->request->getFile('graded_mock');

            if (!empty($ungradedActual)) {
                $this->runUploadExam($exam->id, $ungradedActual, 'ungraded_actual');
            }
            if (!empty($gradedActual)) {
                $this->runUploadExam($exam->id, $gradedActual, 'graded_actual');
            }
            if (!empty($ungradedMock)) {
                $this->runUploadExam($exam->id, $ungradedMock, 'ungraded_mock');
            }
            if (!empty($gradedMock)) {
                $this->runUploadExam($exam->id, $gradedMock, 'graded_mock');
            }
        } else {
            $dateProven = $this->request->getFile('date_proven');
            $identityProven = $this->request->getFile('identity_proven');
            $examProven = $this->request->getFile('exam_proven');

            if (!empty($dateProven)) {
                $this->runUploadExam($id, $dateProven, 'date_proven');
            }
            if (!empty($identityProven)) {
                $this->runUploadExam($id, $identityProven, 'identity_proven');
            }
            if (!empty($examProven)) {
                $this->runUploadExam($id, $examProven, 'exam_proven');
            }
        }

        return $this->getResponse([
                        'message' => 'Exam uploaded successfully',
                    ]);
    }

    /**
     * Update an exsiting exam
     *
     * @return mixed
     */
    public function update(int $id)
    {
        // Validate request parameters.
        $rules = [
            'summary' => 'required',
            'grade_value' => 'required|decimal',
            'discipline_id' => 'required|numeric',
            'subject_id' => 'required|numeric',
            'level_id' => 'required|numeric',
            'prof_first_name' => 'required',
            'prof_last_name' => 'required',
            'prof_web' => 'required',
            'prof_email' => 'required'
        ];

        $input = array(
            'mode' => $this->request->getVar('mode'),
            'group_id' => $this->request->getVar('group_id'),
            'group_name' => $this->request->getVar('group_name'),
            'summary' => $this->request->getVar('summary'),
            'grade_value' => $this->request->getVar('grade_value') ? $this->request->getVar('grade_value') : null,
            'discipline_id' => $this->request->getVar('discipline_id'),
            'subject_id' => $this->request->getVar('subject_id'),
            'level_id' => $this->request->getVar('level_id'),
            'prof_first_name' => $this->request->getVar('prof_first_name'),
            'prof_last_name' => $this->request->getVar('prof_last_name'),
            'prof_web' => $this->request->getVar('prof_web'),
            'prof_email' => $this->request->getVar('prof_email'),
            'questions_and_solutions' => $this->request->getVar('questions_and_solutions'),
        );

        $isGroupLead = false;
        if ($input['mode'] == 'group') {
            $group = $this->groupService->getOneById($input['group_id']);
            $isGroupLead = $group->owner_id == $this->user['id'] ? true : false;
        }

        if (($input['mode'] == 'single') && !$this->validateRequest($input, $rules)) {
            return $this->getResponse(
                $this->validator->getErrors(),
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        if ($input['mode'] == 'single' || $isGroupLead) {
            helper('prof_validate_helper');

            if (!validate_professor($input['prof_web'], $input['prof_email'], $input['prof_first_name'], $input['prof_last_name'])) {
                return $this->getResponse(
                    [ 'message' => "Couldn't authenticate your professor from the prof web address provided!"],
                    ResponseInterface::HTTP_BAD_REQUEST
                );
            }

            $professor = $this->professorService->findOneOrNullByEmail($input['prof_email']);
            $profId = $professor->id;

            if (!empty($professor)) {
                // Update an existing professor
                // $professor = $this->professorService->update(
                //     $professor->id,
                //     $input['prof_first_name'],
                //     $input['prof_last_name'],
                //     $input['prof_email'],
                //     $input['prof_web']
                // );
            } else {
                // Add new professor
                $professor = $this->professorService->create(
                    $input['prof_first_name'],
                    $input['prof_last_name'],
                    $input['prof_email'],
                    $input['prof_web'],
                    true
                );
            }

            $exam = $this->examService->getExamById($id);
            $exam->professor_id = $profId;
            $exam->summary = $input['summary'];
            $exam->grade_value = $input['grade_value'];
            $exam->discipline_id = $input['discipline_id'];
            $exam->subject_id = $input['subject_id'];
            $exam->level_id = $input['level_id'];
            $exam->save();

            // Upload images to cloudinary asyncronously.
            $ungradedActual = $this->request->getFile('ungraded_actual');
            $gradedActual = $this->request->getFile('graded_actual');
            $ungradedMock = $this->request->getFile('ungraded_mock');
            $gradedMock = $this->request->getFile('graded_mock');
            $dateProven = $this->request->getFile('date_proven');
            $identityProven = $this->request->getFile('identity_proven');
            $examProven = $this->request->getFile('exam_proven');

            if (!empty($ungradedActual)) {
                $this->runUploadExam($id, $ungradedActual, 'ungraded_actual');
            }
            if (!empty($gradedActual)) {
                $this->runUploadExam($id, $gradedActual, 'graded_actual');
            }
            if (!empty($ungradedMock)) {
                $this->runUploadExam($id, $ungradedMock, 'ungraded_mock');
            }
            if (!empty($gradedMock)) {
                $this->runUploadExam($id, $gradedMock, 'graded_mock');
            }
            if (!empty($dateProven)) {
                $this->runUploadExam($id, $dateProven, 'date_proven');
            }
            if (!empty($identityProven)) {
                $this->runUploadExam($id, $identityProven, 'identity_proven');
            }
            if (!empty($examProven)) {
                $this->runUploadExam($id, $examProven, 'exam_proven');
            }
        } elseif ($input['mode'] == 'group') {
            // $group = $this->groupService->getOneById($input['group_id']);
            // $group->name = $input['group_name'];
            // $group->save();
        }

        if ($input['mode'] == 'group') {
            // Add & update questions & solutions
            $groupUser = $this->groupService->findOneByGroupAndUser($input['group_id'], $this->user['id']);
            $groupUserId = $groupUser->id;
            $questions_and_solutions = json_decode($input['questions_and_solutions'], true);
            foreach ($questions_and_solutions as $questions_and_solution) {
                if ($questions_and_solution['id']) {
                    $this->groupService->updateQA($questions_and_solution['id'], $questions_and_solution);
                } else {
                    $this->groupService->addQA($groupUserId, $questions_and_solution);
                }
            }
        }

        return $this->getResponse([
                        'status' => true,
                        'message' => 'Exam updated successfully',
                    ]);
    }

    protected function runUploadExam($examId, $uploadFile, $fileType)
    {
        helper('date');
        $timestampDir = now();
        $tmpDir = $this->tempUploadPath . '\\' . $timestampDir;
        $uploadFile->move($tmpDir, $uploadFile->getName());

        $param = array(
            'exam_id' => $examId,
            'file' => $tmpDir . '\\' . $uploadFile->getName(),
            'dir' => $timestampDir,
            'student_first_name' => $this->user['firstName'],
            'student_last_name' => $this->user['lastName'],
            'student_email' => $this->user['email'],
            'student_id' => $this->user['id'],
            'type' => $fileType,
        );
        $this->asyncLib->do_in_background(self::class, 'uploadImages', $param);
    }

    /**
     * Upload image to cloudinary
     */
    public function uploadImages($examId, $filePath, $timestampDir, $studentFirstName, $studentLastName, $studentEmail, $studentId, $type)
    {
        // sleep(3);

        try {
            $withRelations = ['professor', 'discipline', 'subject', 'level'];
            $exam = $this->examService->getExamById($examId, $withRelations);
            $examData = $exam->getFields();

            // Upload images to cloudinary
            $uploadOption = array(
                'folder' => 'temp_quest',
                "resource_type" => "image",
                "format" => "pdf",
                "type" => "authenticated",
                "tags" => array(
                    $examData['discipline']['name'],
                    $examData['subject']['name'],
                    $examData['level']['name']
                ),
                "context" => array(
                    "discipline_name" => $examData['discipline']['name'],
                    "subject_name" => $examData['subject']['name'],
                    "level_name" => $examData['level']['name'],
                    "grade_value" => $examData['grade_value'],
                    "student_first_name" => $studentFirstName,
                    "student_last_name" => $studentLastName,
                    "student_email" => $studentEmail,
                    "prof_first_name" => $examData['professor']['first_name'],
                    "prof_last_name" => $examData['professor']['last_name'],
                    "prof_email" => $examData['professor']['email'],
                    // "identification_id" => $hash_id,
                    // "uploads_id" => $uploads_id,
                    // "student_post_id" => $post_id_found,
                    "caption" => $examData['discipline']['name'].' Exam, by '.$studentFirstName.' '.$studentLastName,
                ),
                "moderation" => "metascan",
                "notification_url" => base_url() . "/api/hook/metascan"
            );

            helper('date');
            $timestamp = now();

            switch ($type) {
                case 'ungraded_actual':
                    $uploadOption['public_id'] = $examId . '_ungraded_actual_' . $timestamp;
                    $uploadOption['context']['alt'] = 'ungraded actual version';
                    $response = $this->cloudLib->upload($filePath, $uploadOption);

                    $this->examService->createUpload(
                        $response['public_id'],
                        'u_act',
                        (int) $examId,
                        'pending'
                    );
                    break;
                case 'graded_actual':
                    $uploadOption['public_id'] = $examId . '_graded_actual_' . $timestamp;
                    $uploadOption['context']['alt'] = 'graded actual version';
                    $response = $this->cloudLib->upload($filePath, $uploadOption);

                    $this->examService->createUpload(
                        $response['public_id'],
                        'g_act',
                        (int) $examId,
                        'pending'
                    );
                    break;
                case 'ungraded_mock':
                    $uploadOption['public_id'] = $examId . '_ungraded_mock_' . $timestamp;
                    $uploadOption['context']['alt'] = 'ungraded mock version';
                    $response = $this->cloudLib->upload($filePath, $uploadOption);

                    $this->examService->createUpload(
                        $response['public_id'],
                        'u_mock',
                        (int) $examId,
                        'pending'
                    );

                    $ungradedSampleUrl = $this->cloudLib->getBluredFile($response['public_id'], $response['pages']);

                    $exam->ungraded_sample_url = $ungradedSampleUrl;
                    $exam->save(false);
                    break;
                case 'graded_mock':
                    $uploadOption['public_id'] = $examId . '_graded_mock_' . $timestamp;
                    $uploadOption['context']['alt'] = 'graded mock version';
                    $response = $this->cloudLib->upload($filePath, $uploadOption);

                    $this->examService->createUpload(
                        $response['public_id'],
                        'g_mock',
                        (int) $examId,
                        'pending'
                    );

                    $gradedSampleUrl = $this->cloudLib->getBluredFile($response['public_id'], $response['pages']);

                    $exam->graded_sample_url = $gradedSampleUrl;
                    $exam->save(false);
                    break;
                case 'date_proven':
                    $uploadOption['public_id'] = $examId . '_date_proven_' . $timestamp;
                    $uploadOption['context']['alt'] = 'group member exam date proven file';
                    $response = $this->cloudLib->upload($filePath, $uploadOption);

                    $groupUser = $this->groupService->getGroupUser($exam->group_id, $studentId);
                    $groupUser->date_proven = $response['public_id'];
                    $groupUser->save();

                    // no break
                case 'identity_proven':
                    $uploadOption['public_id'] = $examId . '_identity_proven_' . $timestamp;
                    $uploadOption['context']['alt'] = 'group member exam identity proven file';
                    $response = $this->cloudLib->upload($filePath, $uploadOption);

                    $groupUser = $this->groupService->getGroupUser($exam->group_id, $studentId);
                    $groupUser->identity_proven = $response['public_id'];
                    $groupUser->save();

                    // no break
                case 'exam_proven':
                    $uploadOption['public_id'] = $examId . '_exam_proven_' . $timestamp;
                    $uploadOption['context']['alt'] = 'group member exam proven file';
                    $response = $this->cloudLib->upload($filePath, $uploadOption);

                    $groupUser = $this->groupService->getGroupUser($exam->group_id, $studentId);
                    $groupUser->exam_proven = $response['public_id'];
                    $groupUser->save();
                    // no break
                default:
                    break;
            }

            // remove temp files with timestamp directory
            helper('filesystem');
            delete_files($this->tempUploadPath . '/' . $timestampDir);
            rmdir($this->tempUploadPath . '/' . $timestampDir);

            // To Do: send email
        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            // To Do: send email
        }
    }

    /**
     * Process the metascan notification request from cloudinary.
     */
    public function processMetascanResult()
    {
        $input = $this->getRequestInput($this->request);

        if ($input['notification_type'] === 'moderation') {
            $this->examService->updateScanStatus(
                $input['public_id'],
                $input['moderation_status']
            );
        }
    }
}
