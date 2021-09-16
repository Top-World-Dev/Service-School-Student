<?php declare(strict_types=1);

namespace App\Services;

use App\Models\PaymentMethod;

/**
 * Payment Method Service
 */
class PaymentMethodService
{
    /**
     * Save a payment method
     *
     * @param int $user_id
     * @param string $email
     * @param string $type
     */
    public function create(int $user_id, string $email, string $type)
    {
        $payment_method = new PaymentMethod();
        $payment_method->user_id = $user_id;
        $payment_method->payment_email = $email;
        $payment_method->type = $type;
        $payment_method->status = 'pending';
        $payment_method->save();
    }

    /**
     * Remove a payment method
     *
     * @param int $id
     */
    public function remove(int $id)
    {
        $payment_method = PaymentMethod::getOneByID($id, []);
        $payment_method->delete();
    }
}