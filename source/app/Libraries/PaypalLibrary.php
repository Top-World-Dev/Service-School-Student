<?php 
namespace App\Libraries;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payout;
use PayPal\Api\PayoutSenderBatchHeader;
use PayPal\Api\PayoutItem;
use PayPal\Api\Currency;

class PaypalLibrary
{
    /**
     * @var ApiContext
     */
    protected $apiContext;

    /**
     * @var Payout
     */
    protected $payouts;

    /**
     * @var PayoutSenderBatchHeader
     */
    protected $senderBatchHeader;

    /**
     * The constructor
     */
    public function __construct()
    {
        $client_id = getenv('paypal.client_id');
        $client_secret = getenv('paypal.client_secret');
        $this->apiContext = new ApiContext(
                new OAuthTokenCredential($client_id, $client_secret)
        );
        $this->payouts = new Payout();
        $this->senderBatchHeader = new PayoutSenderBatchHeader();
        $this->currency = new Currency('{
                                "value":"7.0",
                                "currency":"EUR"
                            }');
    }

    /**
     * Payouts
     *
     * @param array $buyers
     */
    public function payouts(array $buyers)
    {
        helper('date');
        $this->senderBatchHeader->setSenderBatchId(uniqid())
             ->setEmailSubject("You have a Payout!");

        $this->payouts->setSenderBatchHeader($this->senderBatchHeader);

        foreach ($buyers as $buyer) {
            $senderItem = new PayoutItem();
            $senderItem->setRecipientType('Email')
                ->setNote('Thanks for your upload!')
                ->setReceiver($buyer['payment_email'])
                ->setSenderItemId($buyer['match_id'])
                ->setAmount($this->currency);

            $this->payouts->addItem($senderItem);
        }

        try {
            $output = $this->payouts->create(null, $this->apiContext);
            return [
                'success' => true
            ];
        } catch (\Exception $ex) {
            return [
                'success' => false,
                'error' => $ex->getMessage()
            ];
        }
    }
}
