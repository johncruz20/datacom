<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\MarketingSync;

use Magento\Customer\Api\CustomerRepositoryInterface;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;

/**
 * Builds a platform-neutral profile payload. Adapters map it to their own API format.
 */
class PayloadBuilder
{
    /**
     * Constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository
    ) {
    }

    /**
     * Build.
     *
     * @param PetInterface $pet
     * @return array
     */
    public function build(PetInterface $pet): array
    {
        $customer = $this->customerRepository->getById((int)$pet->getCustomerId());

        return [
            'external_id' => 'magento_customer_' . $customer->getId(),
            'email' => $customer->getEmail(),
            'pet' => [
                'id' => $pet->getPetId(),
                'name' => $pet->getName(),
                'species' => $pet->getSpecies(),
                'breed' => $pet->getBreed(),
                'gender' => $pet->getGender(),
                'birthday' => $pet->getDateOfBirth(),
                'age_years' => $this->getAgeInYears($pet->getDateOfBirth()),
                'weight_kg' => $pet->getWeightKg(),
            ],
        ];
    }

    /**
     * Get age in years.
     *
     * @param string|null $dateOfBirth
     * @return int|null
     */
    public function getAgeInYears(?string $dateOfBirth): ?int
    {
        if ($dateOfBirth === null) {
            return null;
        }

        return (new \DateTimeImmutable($dateOfBirth))->diff(new \DateTimeImmutable('today'))->y;
    }
}
