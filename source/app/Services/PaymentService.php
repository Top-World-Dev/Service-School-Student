<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Payment;

/**
 * Payment Service
 */
class PaymentService
{
    /**
     * Save a payment
     *
     * @param int $match_id
     * @param string $email
     * @param string $payment_option
     * @param string|null $pp_batch_id
     * @param string|null $pp_item_id
     * @param float $amount
     * @param int|null $transfer_id
     * @param string $status
     * @param string $type
     */
    public function create(int $match_id, string $email, string $payment_option, ?string $pp_batch_id=null, ?string $pp_item_id=null, float $amount, ?int $transfer_id=null, string $status, string $type)
    {
        $payment = new Payment();
        $payment->match_id = $match_id;
        $payment->email = $email;
        $payment->payment_option = $payment_option;
        $payment->pp_batch_id = $pp_batch_id;
        $payment->pp_item_id = $pp_item_id;
        $payment->amount = $amount;
        $payment->transfer_id = $transfer_id;
        $payment->status = $status;
        $payment->type = $type;
        $payment->save();
    }

    /**
     * Get a payment by ID
     *
     * @param int $transfer_id
     * @param string $status
     * @param Payment
     */
    public function findOneByTransferId(int $transfer_id): Payment
    {
        return Payment::findOne(['transfer_id' => $transfer_id], []);
    }

    /**
     * Get a payment by match ID
     *
     * @param int $match_id
     * @param Payment
     */
    public function findOneByMatchId(int $match_id): Payment
    {
        return Payment::findOne(['match_id' => $match_id], []);
    }


    /**
     * Update status of a payment by transfer ID
     *
     * @param int $transfer_id
     * @param string $status
     * @param bool
     */
    public function updateStatusByTransferId(int $transfer_id, string $status): bool
    {
        $payment = Payment::findOne(['transfer_id' => $transfer_id], []);
        if ($payment) {
            $payment->status = $status;
            $payment->save();
            return true;
        }

        return false;
    }
}