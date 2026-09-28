<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Api;

use PawsWhiskers\PetProfile\Api\Data\PetInterface;

/**
 * Customer-scoped pet operations. Every method enforces that the pet belongs to the given
 * customer; a pet owned by someone else is reported as "not found" so IDs cannot be probed.
 *
 * @api
 */
interface CustomerPetManagementInterface
{
    /**
     * Get all pets owned by the customer.
     *
     * @param int $customerId
     * @return \PawsWhiskers\PetProfile\Api\Data\PetInterface[]
     */
    public function getList(int $customerId): array;

    /**
     * Get one of the customer's pets.
     *
     * @param int $customerId
     * @param int $petId
     * @return \PawsWhiskers\PetProfile\Api\Data\PetInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $customerId, int $petId): PetInterface;

    /**
     * Create (no pet_id) or update (pet_id set) a pet for the customer.
     *
     * @param int $customerId
     * @param \PawsWhiskers\PetProfile\Api\Data\PetInterface $pet
     * @return \PawsWhiskers\PetProfile\Api\Data\PetInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(int $customerId, PetInterface $pet): PetInterface;

    /**
     * Delete one of the customer's pets.
     *
     * @param int $customerId
     * @param int $petId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(int $customerId, int $petId): bool;
}
