<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Api\Data\PetSearchResultsInterface;
use PawsWhiskers\PetProfile\Api\Data\PetSearchResultsInterfaceFactory;
use PawsWhiskers\PetProfile\Api\PetRepositoryInterface;
use PawsWhiskers\PetProfile\Model\ResourceModel\Pet as PetResource;
use PawsWhiskers\PetProfile\Model\ResourceModel\Pet\CollectionFactory;

class PetRepository implements PetRepositoryInterface
{
    /**
     * Constructor.
     *
     * @param PetResource $resource
     * @param PetFactory $petFactory
     * @param CollectionFactory $collectionFactory
     * @param PetSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param PetValidator $validator
     */
    public function __construct(
        private readonly PetResource $resource,
        private readonly PetFactory $petFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly PetSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly PetValidator $validator
    ) {
    }

    /**
     * @inheritDoc
     */
    public function save(PetInterface $pet): PetInterface
    {
        $this->validator->validate($pet);

        try {
            /** @var Pet $pet */
            $this->resource->save($pet);
        } catch (AlreadyExistsException $e) {
            // Unique key hit: a concurrent request saved the same pet first.
            throw new AlreadyExistsException(
                __('A pet named "%1" of this species already exists for this customer.', (string)$pet->getName()),
                $e
            );
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the pet profile.'), $e);
        }

        return $this->getById((int)$pet->getPetId());
    }

    /**
     * @inheritDoc
     */
    public function getById(int $petId): PetInterface
    {
        $pet = $this->petFactory->create();
        $this->resource->load($pet, $petId);
        if (!$pet->getPetId()) {
            throw NoSuchEntityException::singleField(PetInterface::PET_ID, $petId);
        }

        return $pet;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): PetSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(PetInterface $pet): bool
    {
        try {
            /** @var Pet $pet */
            $this->resource->delete($pet);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the pet profile.'), $e);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $petId): bool
    {
        return $this->delete($this->getById($petId));
    }
}
