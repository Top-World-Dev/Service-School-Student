<?php declare(strict_types=1);

namespace App\Services;

/**
 * Mail Service
 */
class MailService
{
    /**
     * @var \Config\Email|mixed
     */
    protected $email;

    /**
     * @var string
     */
    protected $from = 'noreply@xamlinx.com';

    /**
     * @var string
     */
    protected $fromName = 'Xamlinx';

    /**
     * @var string
     */
    protected $front_url;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->email = \Config\Services::email();
        $this->front_url = getenv('app.frontURL');
    }

    /**
     * Send an email verification code
     *
     * @param string $to
     * @param string $verification_code
     */
    public function sendVerifyEmail(string $to, string $verification_code)
    {
        $subject = 'Verify your email';
        $msg = "Please click on below URL or paste into your browser to verify your Email Address\n\n " . $this->front_url . "/verify-email/".$verification_code . "\n" . "\n\nThanks\nXamlinx Team";
        $this->send($to, $subject, $msg);
    }

    /**
     * Send an email to student to upload exams
     *
     * @param string $to
     */
    public function sendForAskingToUpload(string $to)
    {
        $subject = 'Upload exams';
        $msg = 'A student requested to download your exam. Please upload your exams and get paid.';
        $this->send($to, $subject, $msg);
    }

    /**
     * Send an password reset email
     *
     * @param string $to
     * @param string $token
     */
    public function sendPassowrdResetLink(string $to, string $token)
    {
        $subject = 'Reset your password';
        $msg = "Please click on below URL or paste into your browser to reset your password\n\n " . $this->front_url . "/reset-password/" . $token . "\n" . "\n\nThanks\nXamlinx Team";
        $this->send($to, $subject, $msg);
    }

    /**
     * Send an group rejection email
     *
     * @param string $to
     */
    public function sendGroupRejectEmail(string $to)
    {
        $subject = 'Your group exams are rejected';
        $msg = "Please resubmit questions and solutions.  If all the members of the group do not resubmit within 48 hours, then the mock exam should be automatically dismissed by the system.";
        $this->send($to, $subject, $msg);
    }

    /**
     * Send an email
     *
     * @param string $to
     * @param string $subject
     * @param string $msg
     */
    protected function send(string $to, string $subject, string $msg)
    {
        $this->email->setFrom($this->from, $this->fromName);
        $this->email->setTo($to);
        // $this->email->setCC('another@another-example.com');
        // $this->email->setBCC('them@their-example.com');
        $this->email->setSubject($subject);
        $this->email->setMessage($msg);

        $this->email->send();
    }
}