<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\MarketingSync;

use Magento\Framework\Exception\LocalizedException;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Api\MarketingSyncInterface;

/**
 * Fans a change out to every registered platform adapter (configured in di.xml).
 * Adding Braze or another platform is a new adapter plus one di.xml line.
 */
class CompositeSync implements MarketingSyncInterface
{
    /**
     * Constructor.
     *
     * @param MarketingSyncInterface[] $adapters
     * @throws \InvalidArgumentException
     */
    public function __construct(
        private readonly array $adapters = []
    ) {
        foreach ($adapters as $code => $adapter) {
            if (!$adapter instanceof MarketingSyncInterface) {
                throw new \InvalidArgumentException(
                    sprintf('Marketing sync adapter "%s" must implement %s', $code, MarketingSyncInterface::class)
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function upsert(PetInterface $pet): void
    {
        foreach ($this->adapters as $adapter) {
            $adapter->upsert($pet);
        }
    }

    /**
     * @inheritDoc
     */
    public function remove(int $customerId, int $petId): void
    {
        foreach ($this->adapters as $adapter) {
            $adapter->remove($customerId, $petId);
        }
    }
}
