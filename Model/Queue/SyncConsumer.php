<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\Queue;

use Magento\Framework\Exception\NoSuchEntityException;
use PawsWhiskers\PetProfile\Api\Data\PetSyncMessageInterface;
use PawsWhiskers\PetProfile\Api\MarketingSyncInterface;
use PawsWhiskers\PetProfile\Api\PetRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Handler for the paws.pet_profile.sync consumer.
 *
 * Upserts re-read the current pet state rather than trusting a snapshot, so duplicate or
 * out-of-order deliveries converge on the latest data.
 */
class SyncConsumer
{
    /**
     * Constructor.
     *
     * @param PetRepositoryInterface $petRepository
     * @param MarketingSyncInterface $marketingSync
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly PetRepositoryInterface $petRepository,
        private readonly MarketingSyncInterface $marketingSync,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Process one sync message by routing it to the marketing platform adapters.
     *
     * @param PetSyncMessageInterface $message
     * @return void
     * @throws \Exception rethrown so the framework rejects the message and it can be retried
     */
    public function process(PetSyncMessageInterface $message): void
    {
        $context = [
            'pet_id' => $message->getPetId(),
            'customer_id' => $message->getCustomerId(),
            'operation' => $message->getOperation(),
        ];

        try {
            match ($message->getOperation()) {
                PetSyncMessageInterface::OPERATION_UPSERT => $this->upsert($message),
                PetSyncMessageInterface::OPERATION_DELETE => $this->marketingSync->remove(
                    $message->getCustomerId(),
                    $message->getPetId()
                ),
                default => $this->logger->warning('Unknown pet sync operation, message dropped', $context),
            };
        } catch (\Exception $e) {
            $this->logger->error('Pet profile marketing sync failed', $context + ['exception' => $e]);
            throw $e;
        }
    }

    /**
     * Push the pet's current state; skip it if the pet has since been deleted.
     *
     * @param PetSyncMessageInterface $message
     * @return void
     */
    private function upsert(PetSyncMessageInterface $message): void
    {
        try {
            $pet = $this->petRepository->getById($message->getPetId());
        } catch (NoSuchEntityException) {
            // Deleted after this message was published; the delete message handles removal.
            return;
        }

        $this->marketingSync->upsert($pet);
    }
}
