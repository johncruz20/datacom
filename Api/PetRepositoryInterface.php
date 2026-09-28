<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Api\Data\PetSearchResultsInterface;

/**
 * Persistence contract for pet profiles. Not customer-scoped: intended for trusted callers
 * (admin integrations, segmentation jobs). Customer-facing code uses CustomerPetManagementInterface.
 *
 * @api
 */
interface PetRepositoryInterface
{
    /**
     * Validate and save a pet.
     *
     * @param \PawsWhiskers\PetProfile\Api\Data\PetInterface $pet
     * @return \PawsWhiskers\PetProfile\Api\Data\PetInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(PetInterface $pet): PetInterface;

    /**
     * Load a pet by ID.
     *
     * @param int $petId
     * @return \PawsWhiskers\PetProfile\Api\Data\PetInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $petId): PetInterface;

    /**
     * Find pets matching the search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \PawsWhiskers\PetProfile\Api\Data\PetSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): PetSearchResultsInterface;

    /**
     * Delete a pet.
     *
     * @param \PawsWhiskers\PetProfile\Api\Data\PetInterface $pet
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(PetInterface $pet): bool;

    /**
     * Delete a pet by ID.
     *
     * @param int $petId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $petId): bool;
}
