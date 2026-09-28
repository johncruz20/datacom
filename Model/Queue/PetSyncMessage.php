<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\Queue;

use Magento\Framework\DataObject;
use PawsWhiskers\PetProfile\Api\Data\PetSyncMessageInterface;

class PetSyncMessage extends DataObject implements PetSyncMessageInterface
{
    /**
     * @inheritDoc
     */
    public function getPetId(): int
    {
        return (int)$this->getData('pet_id');
    }

    /**
     * @inheritDoc
     */
    public function setPetId(int $petId): PetSyncMessageInterface
    {
        return $this->setData('pet_id', $petId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): int
    {
        return (int)$this->getData('customer_id');
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId): PetSyncMessageInterface
    {
        return $this->setData('customer_id', $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getOperation(): string
    {
        return (string)$this->getData('operation');
    }

    /**
     * @inheritDoc
     */
    public function setOperation(string $operation): PetSyncMessageInterface
    {
        return $this->setData('operation', $operation);
    }

    /**
     * @inheritDoc
     */
    public function getOccurredAt(): string
    {
        return (string)$this->getData('occurred_at');
    }

    /**
     * @inheritDoc
     */
    public function setOccurredAt(string $occurredAt): PetSyncMessageInterface
    {
        return $this->setData('occurred_at', $occurredAt);
    }
}
