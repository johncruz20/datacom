<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use PawsWhiskers\PetProfile\Api\CustomerPetManagementInterface;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Api\PetRepositoryInterface;
use PawsWhiskers\PetProfile\Model\ResourceModel\Pet as PetResource;

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
     */
    public function __construct(
        private readonly PetRepositoryInterface $petRepository,
        private readonly PetResource $petResource,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly SortOrderBuilder $sortOrderBuilder,
        private readonly Config $config
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
