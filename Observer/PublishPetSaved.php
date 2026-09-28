<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Api\Data\PetSyncMessageInterface;
use PawsWhiskers\PetProfile\Model\Queue\SyncPublisher;

/**
 * Listens to paws_pet_profile_save_commit_after, so the message is only published
 * after the row is committed and the consumer can never read uncommitted data.
 */
class PublishPetSaved implements ObserverInterface
{
    /**
     * Constructor.
     *
     * @param SyncPublisher $syncPublisher
     */
    public function __construct(
        private readonly SyncPublisher $syncPublisher
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        $pet = $observer->getEvent()->getData('pet');
        if ($pet instanceof PetInterface) {
            $this->syncPublisher->publish($pet, PetSyncMessageInterface::OPERATION_UPSERT);
        }
    }
}
