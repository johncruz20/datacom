<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private const XML_PATH_MAX_PETS = 'paws_pet_profile/general/max_pets_per_customer';
    private const XML_PATH_SYNC_ENABLED = 'paws_pet_profile/general/marketing_sync_enabled';

    /**
     * Constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Get max pets per customer.
     *
     * @return int
     */
    public function getMaxPetsPerCustomer(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_MAX_PETS);
    }

    /**
     * Is marketing sync enabled.
     *
     * @return bool
     */
    public function isMarketingSyncEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SYNC_ENABLED);
    }
}
