<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use PawsWhiskers\PetProfile\Api\CustomerPetManagementInterface;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Api\PetRepositoryInterface;
use PawsWhiskers\PetProfile\Model\ResourceModel\Pet as PetResource;
use PawsWhiskers\PetProfile\Model\Source\Species;

class CustomerPetManagement implements CustomerPetManagementInterface
{
    /**
     * Constructor.
     *
     * @param PetRepositoryInterface $petRepository
     * @param PetResource $petResource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param Config $config
     * @param Species $species
     */
    public function __construct(
        private readonly PetRepositoryInterface $petRepository,
        private readonly PetResource $petResource,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly SortOrderBuilder $sortOrderBuilder,
        private readonly Config $config,
        private readonly Species $species
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getList(int $customerId): array
    {
        $sortOrder = $this->sortOrderBuilder->setField(PetInterface::PET_ID)->setAscendingDirection()->create();
        $criteria = $this->searchCriteriaBuilder
            ->addFilter(PetInterface::CUSTOMER_ID, $customerId)
            ->addSortOrder($sortOrder)
            ->create();

        return array_values($this->petRepository->getList($criteria)->getItems());
    }

    /**
     * @inheritDoc
     */
    public function get(int $customerId, int $petId): PetInterface
    {
        $pet = $this->petRepository->getById($petId);
        if ($pet->getCustomerId() !== $customerId) {
            // Same error as a missing pet: don't reveal that the ID exists for another customer.
            throw NoSuchEntityException::singleField(PetInterface::PET_ID, $petId);
        }

        return $pet;
    }

    /**
     * @inheritDoc
     */
    public function save(int $customerId, PetInterface $pet): PetInterface
    {
        if ($pet->getPetId()) {
            $this->get($customerId, $pet->getPetId());
        } else {
            $this->assertBelowPetLimit($customerId);
        }

        // Ownership always comes from the authenticated context, never from the payload.
        $pet->setCustomerId($customerId);
        if ($pet->getName() !== null) {
            $pet->setName(trim($pet->getName()));
        }
        $this->assertNotDuplicate($pet);

        return $this->petRepository->save($pet);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $customerId, int $petId): bool
    {
        return $this->petRepository->delete($this->get($customerId, $petId));
    }

    /**
     * A customer can't have two pets with the same name and species (case-insensitive).
     *
     * The unique key on (customer_id, name, species) backs this up for concurrent requests.
     *
     * @param PetInterface $pet
     * @return void
     * @throws AlreadyExistsException
     */
    private function assertNotDuplicate(PetInterface $pet): void
    {
        $name = (string)$pet->getName();
        $species = (string)$pet->getSpecies();
        if ($name === '' || $species === '') {
            return; // Required-field errors are reported by the validator.
        }

        if ($this->petResource->hasPetNamed((int)$pet->getCustomerId(), $name, $species, $pet->getPetId())) {
            throw new AlreadyExistsException(
                __('You already have a %1 named "%2".', mb_strtolower($this->getSpeciesLabel($species)), $name)
            );
        }
    }

    /**
     * Species label for messages, falling back to the code.
     *
     * @param string $code
     * @return string
     */
    private function getSpeciesLabel(string $code): string
    {
        foreach ($this->species->toOptionArray() as $option) {
            if ($option['value'] === $code) {
                return (string)$option['label'];
            }
        }

        return $code;
    }

    /**
     * Assert below pet limit.
     *
     * @param int $customerId
     * @return void
     * @throws InputException
     */
    private function assertBelowPetLimit(int $customerId): void
    {
        $limit = $this->config->getMaxPetsPerCustomer();
        if ($limit > 0 && $this->petResource->countByCustomerId($customerId) >= $limit) {
            throw new InputException(__('You can save up to %1 pets.', $limit));
        }
    }
}
