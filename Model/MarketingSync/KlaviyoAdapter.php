<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\MarketingSync;

use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Api\MarketingSyncInterface;
use Psr\Log\LoggerInterface;

/**
 * Klaviyo adapter stub. Live API configuration is out of scope, so this logs the request it
 * would send. The real implementation would POST to Klaviyo's Profiles API with the pet list
 * stored as a custom profile property keyed by pet ID (making upsert/remove idempotent).
 */
class KlaviyoAdapter implements MarketingSyncInterface
{
    /**
     * Constructor.
     *
     * @param PayloadBuilder $payloadBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly PayloadBuilder $payloadBuilder,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function upsert(PetInterface $pet): void
    {
        $payload = $this->payloadBuilder->build($pet);
        // Don't log the email: log only identifiers and non-personal pet attributes.
        unset($payload['email']);
        $this->logger->info('[Klaviyo] upsert pet', $payload);
    }

    /**
     * @inheritDoc
     */
    public function remove(int $customerId, int $petId): void
    {
        $this->logger->info('[Klaviyo] remove pet', ['customer_id' => $customerId, 'pet_id' => $petId]);
    }
}
