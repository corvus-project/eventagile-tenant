<?php

namespace App\Services;

interface PaymentService
{
    /**
     * Charge a customer.
     *
     * @return array{success: bool, transaction_id: ?string, message: string, code?: string, retryable?: bool, retry_after?: ?int, amount?: float, metadata?: array}
     */
    public function charge(string $customerId, float $amount, string $currency, array $metadata = []): array;

    /**
     * Refund a transaction.
     *
     * @return array{success: bool, transaction_id: string, message: string}
     */
    public function refund(string $transactionId, float $amount = null): array;

    /**
     * Get status of a transaction by ID.
     *
     * @return array{status: string, transaction_id: string, amount: float, message: string}
     */
    public function getStatus(string $transactionId): array;

    /**
     * Return the last gateway response for debugging/logging.
     */
    public function getLastResponse(): ?array;
}
