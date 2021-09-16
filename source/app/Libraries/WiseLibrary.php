<?php 
namespace App\Libraries;

use App\Services\PaymentService;
use Config\Services;
use Ramsey\Uuid\Uuid;

class WiseLibrary
{
    /**
     * @var string
     */
    protected $base_url;

    /**
     * @var string
     */
    protected $api_token;

    /**
     * @var CURLRequest
     */
    protected $client;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * @var string
     */
    protected $payout_currency = 'EUR';

    /**
     * @var float
     */
    protected $payout_amount = 1.8;

    /**
     * The constructor
     */
    public function __construct()
    {
        $this->base_url = getenv('transferwise.base_url');
        $this->api_token = getenv('transferwise.api_token');
        $this->headers = array(
            'Authorization' => 'Bearer ' . $this->api_token,
            'content-type'  => 'application/json',
        );
        $options = [
            'baseURI' => $this->base_url,
        ];
        $this->client = Services::curlrequest($options);
        $this->paymentService = new PaymentService();
    }

    /**
     * Payouts
     *
     * @param array $recipients
     */
    public function payouts(array $recipients)
    {
        $profile = $this->get_payable_profile();
        foreach ($recipients as $recipient) {
            $target_currency = $recipient['currency'];
            $quote = $this->create_quote($profile['id'], $this->payout_currency, $target_currency, $this->payout_amount, null);
            $recipient_name = $recipient['user']['firstName'] . ' ' . $recipient['user']['lastName'];
            $created_recipient = $this->create_recipient($profile['id'], $recipient_name, $target_currency, $recipient['payment_email']);
            // $transfer_requirements = $this->get_transfer_requirements($recipient['id'], $quote['id'], $recipient['payment_email']);
            $transfer = $this->create_transfer($created_recipient['id'], $quote['id'], $recipient['payment_email']);

            if (!empty($transfer['errors'])) {
                return [
                    'recipient'  => $recipient,
                    'errors' => $transfer['errors']
                ];
            } else {
                $this->paymentService->create(
                    $recipient['match_id'],
                    $recipient['payment_email'],
                    'transferwise',
                    null,
                    null,
                    $this->payout_amount,
                    $transfer['id'],
                    'initial',
                    'payout'
                );

                $fund = $this->fund_transfer($profile['id'], $transfer['id']);
                return $fund;
            }
        }
        return $profile;
    }

    /**
     * Get payable profile.
     * Choose business profile by default, otherwise personal profile.
     *
     * @param array $emails
     */
    public function get_payable_profile()
    {
        $profiles = $this->request('GET', '/v1/profiles');
        $payable_profile = $this->searchArrayByValue($profiles, 'type', 'business');
        if (!$payable_profile) {
            $payable_profile = $this->searchArrayByValue($profiles, 'type', 'personal');
        }

        return $payable_profile;
    }

    /**
     * Create a quote
     *
     * @param int $profile_id
     * @param string $source_currency
     * @param string $target_currency
     * @param float|null $source_amount
     * @param float|null $target_amount
     */
    public function create_quote(int $profile_id, string $source_currency, string $target_currency, ?float $source_amount=null, ?float $target_amount=null)
    {
        $data = array(
            'profile' => $profile_id,
            'sourceCurrency' => $source_currency,
            'targetCurrency'   => $target_currency,
            'targetAmount'   => $target_amount,
            'sourceAmount' => $source_amount
        );

        $response = $this->request('POST', '/v2/quotes', $data);

        return $response;
    }

    /**
     *  Create a recipient account
     *
     * @param int $profile_id
     * @param string $account_name
     * @param string $currency
     * @param string $email
     */
    public function create_recipient(int $profile_id, string $account_name, string $currency, string $email)
    {
        $data = array(
            'profile'   => $profile_id,
            'accountHolderName'   => $account_name,
            'currency' => $currency,
            'type' => 'email',
            'details' => [ 
                'email' => $email,
                'reference'   => 'xamlinx',
            ]
        );

        $response = $this->request('POST', '/v1/accounts', $data);

        return $response;
    }

    /**
     *  Get transfer requirements
     *
     * @param int $target_account_id
     * @param string $quote_uuid
     * @param string $email
     */
    public function get_transfer_requirements(int $target_account_id, string $quote_uuid, string $email)
    {
        $data = array(
            'targetAccount'   => $target_account_id,
            'quoteUuid'   => $quote_uuid,
            'customerTransactionId' => Uuid::uuid4(),
            'details' => [
                'reference'   => 'xamlinx',
                'transferPurpose'   => 'verification.transfers.purpose.other',
                'sourceOfFunds'   => 'verification.source.of.funds.other',
            ]
        );

        $response = $this->request('POST', '/v1/transfer-requirements', $data);

        $requirements = [];
        // foreach ($response as $requirement) {
        //     foreach ($requirement['fields'] as $field) {
        //         foreach ($field['group'] as $item) {
        //             if ($item['required'] && $item['key'] === 'transferPurpose') {
        //                 array_push($requirements, );
        //             }
        //         }
        //     }
        // }

        return $response;
    }

    /**
     *  Create a transfer
     *
     * @param int $target_account_id
     * @param string $quote_uuid
     * @param string $email
     */
    public function create_transfer(int $target_account_id, string $quote_uuid, string $email)
    {
        $data = array(
            'targetAccount'   => $target_account_id,
            'quoteUuid'   => $quote_uuid,
            'customerTransactionId' => Uuid::uuid4(),
            'details' => [
                'reference'   => 'xamlinx', 
                'transferPurpose'   => 'verification.transfers.purpose.other',
                'sourceOfFunds'   => 'verification.source.of.funds.other',
            ]
        );

        $response = $this->request('POST', '/v1/transfers', $data);

        return $response;
    }

    /**
     *  Fund a transfer
     *
     * @param int $profile_id
     * @param int $transfer_id
     */
    public function fund_transfer(int $profile_id, int $transfer_id)
    {
        $data = array(
            'type' => 'BALANCE'
        );

        $url = '/v3/profiles/' . $profile_id . '/transfers/' . $transfer_id . '/payments';

        $response = $this->request('POST', $url, $data);

        return $response;
    }

    /**
     * Search object array by it's value
     *
     * @param string $method
     * @param string $url
     * @param array|null $body
     * @return mixed
     */
    private function request(string $method, string $url, ?array $body = null) {
        if ($method === 'POST') {
            $this->client->setBody(json_encode($body));
        }

        $response = $this->client->request($method, $url, [
            'headers' => $this->headers,
            'http_errors' => false
        ]);

        return json_decode($response->getBody(), true);
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
}
