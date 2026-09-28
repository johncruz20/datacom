<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\Queue;

use Magento\Framework\MessageQueue\PublisherInterface;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Api\Data\PetSyncMessageInterface;
use PawsWhiskers\PetProfile\Api\Data\PetSyncMessageInterfaceFactory;
use PawsWhiskers\PetProfile\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Publishes pet changes to the marketing sync topic.
 */
class SyncPublisher
{
    public const TOPIC = 'paws.pet_profile.sync';

    /**
     * Constructor.
     *
     * @param PublisherInterface $publisher
     * @param PetSyncMessageInterfaceFactory $messageFactory
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly PublisherInterface $publisher,
        private readonly PetSyncMessageInterfaceFactory $messageFactory,
        private readonly Config $config,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Publish a pet change to the marketing sync topic.
     *
     * @param PetInterface $pet
     * @param string $operation One of PetSyncMessageInterface::OPERATION_*
     * @return void
     */
    public function publish(PetInterface $pet, string $operation): void
    {
        if (!$this->config->isMarketingSyncEnabled() || !$pet->getPetId() || !$pet->getCustomerId()) {
            return;
        }

        $message = $this->messageFactory->create()
            ->setPetId($pet->getPetId())
            ->setCustomerId($pet->getCustomerId())
            ->setOperation($operation)
            ->setOccurredAt(gmdate(DATE_ATOM));

        try {
            $this->publisher->publish(self::TOPIC, $message);
        } catch (\Exception $e) {
            // The pet is already committed; a broker outage must not fail the customer's request.
            // See README "Reliability" for the outbox/reconciliation follow-up.
            $this->logger->error('Pet profile sync publish failed', [
                'pet_id' => $pet->getPetId(),
                'operation' => $operation,
                'exception' => $e,
            ]);
        }
    }
}
