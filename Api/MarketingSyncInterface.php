<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Api;

use PawsWhiskers\PetProfile\Api\Data\PetInterface;

/**
 * SPI implemented by each external marketing platform adapter (Klaviyo, Braze, ...).
 *
 * Implementations must be idempotent: the same call may be delivered more than once.
 *
 * @api
 */
interface MarketingSyncInterface
{
    /**
     * Create or update the pet on the customer's marketing profile.
     *
     * @param \PawsWhiskers\PetProfile\Api\Data\PetInterface $pet
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException on a failure worth retrying
     */
    public function upsert(PetInterface $pet): void;

    /**
     * Remove the pet from the customer's marketing profile.
     *
     * @param int $customerId
     * @param int $petId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException on a failure worth retrying
     */
    public function remove(int $customerId, int $petId): void;
}
