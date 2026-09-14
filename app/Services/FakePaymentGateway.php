<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class FakePaymentGateway implements PaymentService
{
    public function charge(string $customerId, float $amount, string $currency, array $metadata = []): array
    {
        $failRate = (float) config('payment.fail_rate', 0);
        $isMaintenance = config('payment.maintenance_mode', false);

        if ($isMaintenance) {
            Log::info('Payment gateway in maintenance mode. Charge declined.', ['customer_id' => $customerId, 'amount' => $amount]);

            return $this->failedResponse(
                'Payment gateway is currently in maintenance mode. Please try again later.',
                'maintenance_mode',
                true,
                300
            );
        }

        if (random_int(1, 100) <= ($failRate * 100)) {
            $failureCodes = ['gateway_timeout', 'temporary_network_error', 'gateway_unavailable'];
            $code = $failureCodes[array_rand($failureCodes)];
            Log::info('Payment charge failed (retryable).', ['customer_id' => $customerId, 'amount' => $amount, 'code' => $code]);

            return $this->failedResponse(
                'Payment gateway unavailable. Please try again later.',
                $code,
                true,
                300
            );
        }

        $transactionId = 'txn_' . uniqid() . '_' . str_random(8);
        Log::info('Payment charge succeeded.', ['transaction_id' => $transactionId, 'customer_id' => $customerId, 'amount' => $amount]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'Payment successful',
            'amount' => $amount,
            'currency' => $currency,
            'metadata' => $metadata,
        ];
    }

    public function refund(string $transactionId, float $amount = null): array
    {
        Log::info('Payment refund processed.', ['transaction_id' => $transactionId, 'amount' => $amount]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'Refund processed successfully',
        ];
    }

    public function getStatus(string $transactionId): array
    {
        $transaction = PaymentTransaction::where('transaction_id', $transactionId)->first();

        if (! $transaction) {
            return [
                'status' => 'unknown',
                'transaction_id' => $transactionId,
                'amount' => 0,
                'message' => 'Transaction not found',
            ];
        }

        return [
            'status' => $transaction->status,
            'transaction_id' => $transaction->transaction_id,
            'amount' => $transaction->amount,
            'message' => $transaction->status === 'succeeded' ? 'Payment succeeded' : ($transaction->status === 'failed' ? 'Payment failed' : 'Payment ' . $transaction->status),
        ];
    }

    public function getLastResponse(): ?array
    {
        return session('last_payment_response');
    }

    protected function failedResponse(string $message, string $code, bool $retryable = false, ?int $retryAfter = null): array
    {
        $response = [
            'success' => false,
            'transaction_id' => null,
            'message' => $message,
            'code' => $code,
            'retryable' => $retryable,
        ];

        if ($retryAfter) {
            $response['retry_after'] = $retryAfter;
        }

        return $response;
    }
}
