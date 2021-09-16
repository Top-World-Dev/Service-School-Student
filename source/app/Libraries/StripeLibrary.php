<?php 
namespace App\Libraries;

use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Checkout\Session;
 
class StripeLibrary
{
    /**
     * @var string
     */
    protected $front_url;

    /**
     * @var StripeClient
     */
    protected $stripe;

    /**
     * @var float
     */
    protected $single_mode_price = 1500; // $15

    /**
     * @var float
     */
    protected $group_mode_price = 3500; // $35

    /**
     * @var float
     */
    protected $premium_price = 7500; // $75

    /**
     * The constructor
     */
    public function __construct()
    {
        // helper('url');
        $this->front_url = getenv('app.frontURL');
        $secret_key = getenv('stripe.secret_key');
        Stripe::setApiKey($secret_key);
        // $this->stripe = new StripeClient($secret_key);
    }

    /**
     * Create checkout session
     *
     * @param string $customer_email
     * @param string $plan
     */
    public function createSession(string $customer_email, string $plan) {
        $callback_url = $this->front_url . '/requests/purchase';// . $request_id . '/purchase/' . $exam_id;

        $price = $this->single_mode_price;

        switch ($plan) {
            case 'single':
                $price = $this->single_mode_price;
                break;
            case 'group':
                $price = $this->group_mode_price;
                break;
            case 'premium':
                $price = $this->premium_price;
                break;
            default:
                break;
        }

        return Session::create([
            'payment_method_types' => ['card'],
            "customer_email" => $customer_email,
            'line_items' => [[
                'name' => 'xamlinx',
                'description' => 'xamlinx mock exam access',
                'amount' => $price,
                'currency' => 'usd',
                'quantity' => 1,
            ]],
            'success_url' => $callback_url . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $callback_url . '?cancel=' . true,
        ]);
    }
}
