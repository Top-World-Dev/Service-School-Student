<?php

namespace App\Controllers\Admin;

use App\Models\Exam;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\GroupExam;
use App\Controllers\BaseController;
use App\Services\ExamService;
use App\Services\GroupService;
use App\Services\MailService;
use App\Libraries\AsyncLibrary;
use App\Libraries\CloudinaryLibrary;
use App\Libraries\PDFLibrary;
use CodeIgniter\HTTP\ResponseInterface;

class GroupController extends BaseController
{
    /**
     * @var GroupService
     */
    protected $groupService;

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
     * @var ExamService
     */
    protected $examService;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->asyncLib = new AsyncLibrary();
        $this->mailService = new MailService();
        $this->cloudLib = new CloudinaryLibrary();
        $this->examService = new ExamService();
        $this->groupService = new GroupService();
    }

    /**
     * Get all groups
     *
     * @return mixed
     */
    public function index()
    {
        $groups = $this->groupService->getAll();
        return $this->getResponse($groups);
    }

    /**
     * Generate PDF
     *
     * @param int $id
     * @return mixed
     */
    public function generatePDF(int $id)
    {
        $pdfLib = new PDFLibrary();
        $ungradedFileName = 'group_' . $id . '_ungraded_exam.pdf';
        $gradedFileName = 'group_' . $id . '_graded_exam.pdf';
        $ungradedUrl = base_url() . '/render-ungraded-exam/' . $id;
        $gradedUrl = base_url() . '/render-graded-exam/' . $id;
        $ungradedfilePath = $pdfLib->createPDF($ungradedUrl, $gradedFileName);
        $gradedfilePath = $pdfLib->createPDF($gradedUrl, $gradedFileName);

        $param = array(
            'group_id' => $id,
            'ungraded_file_path' => $ungradedfilePath,
            'graded_file_path' => $gradedfilePath
        );
        $this->asyncLib->do_in_background(self::class, 'uploadPDF', $param);

        return $this->getResponse(['status' => true]);
    }

    /**
     * Render HTML with mathjax
     *
     * @param int $id
     * @return mixed
     */
    public function renderUngradedExam(int $id)
    {
        $qas = $this->groupService->getQAsByGroupId($id);
        return view('ungraded_exam_pdf', ['qas' => $qas]);
    }

    /**
     * Render HTML with mathjax
     *
     * @param int $id
     * @return mixed
     */
    public function renderGradedExam(int $id)
    {
        $qas = $this->groupService->getQAsByGroupId($id);
        return view('graded_exam_pdf', ['qas' => $qas]);
    }

    /**
     * Upload PDF
     */
    public function uploadPDF($groupId, $ungradedfilePath, $gradedfilePath)
    {
        $exam = $this->examService->getExamByGroupId($groupId, ['discipline', 'level', 'subject', 'professor']);
        $examData = $exam->getFields();

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
                "prof_first_name" => $examData['professor']['first_name'],
                "prof_last_name" => $examData['professor']['last_name'],
                "prof_email" => $examData['professor']['email'],
                "caption" => $examData['discipline']['name'].' Exam by Group'
            ),
        );

        helper('date');
        $timestamp = now();
        $uploadOption['public_id'] = $groupId . '_group_ungraded_' . $timestamp;
        $uploadOption['context']['alt'] = 'group member exam ungraded exam';
        $ungradedUpload = $this->cloudLib->upload($ungradedfilePath, $uploadOption);
        $uploadOption['public_id'] = $groupId . '_group_graded_' . $timestamp;
        $uploadOption['context']['alt'] = 'group member exam graded exam';
        $gradedUpload = $this->cloudLib->upload($gradedfilePath, $uploadOption);

        $this->examService->createUpload(
            $ungradedUpload['public_id'],
            'u_mock',
            (int) $examData['id'],
            'approved' // group pdf is internally generated and therefore this upload virtually has no risk
        );

        $this->examService->createUpload(
            $gradedUpload['public_id'],
            'g_mock',
            (int) $examData['id'],
            'approved' // group pdf is internally generated and therefore this upload virtually has no risk
        );
        $ungradedSampleUrl = $this->cloudLib->getBluredFile($ungradedUpload['public_id'], $ungradedUpload['pages']);
        $gradedSampleUrl = $this->cloudLib->getBluredFile($gradedUpload['public_id'], $gradedUpload['pages']);
        $exam->ungraded_sample_url = $ungradedSampleUrl;
        $exam->graded_sample_url = $gradedSampleUrl;
        $exam->save(false);

        // remove temp files with timestamp directory
        helper('filesystem');
        delete_files($ungradedfilePath);
        delete_files($gradedfilePath);
    }

    /**
     * Set a quality of the group
     *
     * @return mixed
     */
    public function setQuality()
    {
        $rules = [
            'group_id' => 'required|numeric',
            'quality' => 'required|string',
        ];

        $input = $this->getRequestInput($this->request);

        if (!$this->validateRequest($input, $rules)) {
            return $this
                ->getResponse(
                    $this->validator->getErrors(),
                    ResponseInterface::HTTP_BAD_REQUEST
                );
        }

        $group = $this->groupService->getOneById($input['group_id']);
        $group->quality = $input['quality'];

        switch ($input['quality']) {
            case Group::QUALITY_POOR:
                $group->status = Group::STATUS_IN_PROGRESS;
                $group->rejected_at = new \DateTimeImmutable();
                break;
            case Group::QUALITY_UNEVEN:
                $group->status = Group::STATUS_IN_PROGRESS;
                $group->rejected_at = new \DateTimeImmutable();
                break;
            default:
                break;
        }
        $group->save();

        $param = array(
            'groupId' => $input['group_id'],
        );
        $this->asyncLib->do_in_background(self::class, 'sendRjectEmail', $param);

        return $this->getResponse(['status' => true]);
    }

    /**
     * Send an email about rejection of group.
     *
     * @param string $groupId
     */
    public function sendRjectEmail(int $groupId)
    {
        $members = $this->groupService->getGroupUsers($groupId, ['user']);
        $memberIds = array_map(function($member) {
            return $member['user']['email'];
        }, $members);
        $this->mailService->sendGroupRejectEmail(implode(', ', $memberIds));
    }

    /**
     * Suspend a group member
     *
     * @return mixed
     */
    public function suspendMember()
    {
        // $rules = [
        //     'group_id' => 'required|numeric',
        //     'member_id' => 'required|numeric',
        // ];

        // $input = $this->getRequestInput($this->request);

        // if (!$this->validateRequest($input, $rules)) {
        //     return $this
        //         ->getResponse(
        //             $this->validator->getErrors(),
        //             ResponseInterface::HTTP_BAD_REQUEST
        //         );
        // }

        // $isGroupOwner = $this->groupService->isGroupOwner($input['group_id'], $input['member_id']);
        // if($isGroupOwner) {
        //     $this->groupService->delete($input['group_id']);
        // } else {
        //     $this->groupService->deleteMember($input['member_id']);
        // }
    }
}
