<?php

namespace App\Controllers;

use App\Libraries\PaypalLibrary;
use App\Libraries\WiseLibrary;
use App\Services\MatchService;
use App\Services\PaymentService;

class PayoutController extends BaseController
{
    /**
     * @var PaypalLibrary
     */
    protected $paypalLib;

    /**
     * @var WiseLibrary
     */
    protected $wiseLib;

    /**
     * @var MatchService
     */
    protected $matchService;

    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * The constructor
     *
     */
    public function __construct()
    {
        $this->paypalLib = new PaypalLibrary();
        $this->wiseLib = new WiseLibrary();
        $this->matchService = new MatchService();
        $this->paymentService = new PaymentService();
    }

    /**
     * Get all payable users.
     *
     * @return mixed
     */
    public function index()
    {
        $matches = $this->matchService->getPaidMatches();
        return $this->getResponse($matches);
    }

    /**
     * Payout
     *
     * @return mixed
     */
    public function payout()
    {
        $input = $this->getRequestInput($this->request);
        $matches = $this->matchService->getPaidMatches();

        if ($input['payment_option'] == 'paypal') {
            // Payouts by paypal
            $recipients = $this->getRecipients($matches, 'paypal');
            if ($recipients) {
                $output = $this->paypalLib->payouts($recipients);
                return $this->getResponse(['status' => $output]);
            }
        } else if($input['payment_option'] == 'transferwise') {
            // payouts by wise
            $recipients = $this->getRecipients($matches, 'transferwise');
            if ($recipients) {
                $output = $this->wiseLib->payouts($recipients);
                return $this->getResponse(['status' => $output]);
            }
        }

        return $this->getResponse(['status' => false]);
    }

    /**
     * Get recipients
     *
     * @param array $matches
     * @param string $type
     * @return array
     */
    private function getRecipients(array $matches, string $type) {
        $result = array_map(function($match) use ($type) {
            $payment_methods = $match['payment_methods'];
            $payment_method = $this->searchArrayByValue($payment_methods, 'type', $type);
            if ($payment_method) {
                return [
                    'match_id' => $match['id'],
                    'user' => $match['request']['student'],
                    'payment_email' => $payment_method['payment_email'],
                    'currency' => $match['exam']['school']['country']['code']
                ];
            }
            return null;
        }, $matches);

        return array_filter($result);
    }

    /**
     * Get paid match ids
     *
     * @param array $matches
     * @param string $type
     * @return array
     */
    private function getPaidMatchIds(array $matches, string $type) {
        $result = array_map(function($match) use ($type) {
            $payment_methods = $match['payment_methods'];
            $payment_method = $this->searchArrayByValue($payment_methods, 'type', $type);
            if ($payment_method) {
                return $match['id'];
            }
            return null;
        }, $matches);

        return array_filter($result);
    }

    /**
     * Search object array by it's value
     *
     * @param array $array
     * @param string $key
     * @param string $value
     * @return mixed
     */
    private function searchArrayByValue(array $array, string $key, string $value) {
        $result = null;
        foreach($array as $item) {
            if ($item[$key] === $value) {
                $result = $item;
                break;
            }
        }
        return $result;
    }

    /**
     * Process paypal webhook
     *
     */
    public function processPaypalResult()
    {
        $input = $this->getRequestInput($this->request);
        $event_type = $input['event_type'];
        $this->paymentService->create(
            $input['resource']['payout_item']['sender_item_id'],
            $input['resource']['payout_item']['receiver'],
            'paypal',
            $input['resource']['payout_batch_id'],
            $input['resource']['payout_item_id'],
            strval($input['resource']['payout_item']['amount']['value']),
            null,
            $input['resource']['transaction_status'],
            'payout'
        );

        if ($event_type === 'PAYMENT.PAYOUTS-ITEM.SUCCEEDED') {
            $this->matchService->markAsPaid($input['resource']['payout_item']['sender_item_id']);
        }
    }

    /**
     * Process tranferwise webhook
     *
     */
    public function processWiseResult()
    {
        $input = $this->getRequestInput($this->request);
        $event_type = $input['event_type'];
        log_message('error', json_encode($input));
        if ($event_type === 'transfers#state-change') {
            $this->paymentService->updateStatusByTransferId($input['data']['resource']['id'], $input['data']['current_state']);

            if ($input['data']['current_state'] === 'outgoing_payment_sent') {
                $payment = $this->paymentService->findOneByTransferId($input['data']['resource']['id']);
                if ($payment) {
                    $this->matchService->markAsPaid($payment->match_id);
                }
            }
        } else if ($event_type === 'transfers#active-cases') {
            $this->paymentService->updateStatusByTransferId($input['data']['resource']['id'], json_encode($input['data']['active_cases']));
        }
    }
}
