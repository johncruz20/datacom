<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;

class Pet extends AbstractDb
{
    public const TABLE_NAME = 'paws_pet_profile';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, PetInterface::PET_ID);
    }

    /**
     * Count pets owned by a customer (used to enforce the per-customer limit).
     *
     * @param int $customerId
     * @return int
     */
    public function countByCustomerId(int $customerId): int
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), ['COUNT(*)'])
            ->where(PetInterface::CUSTOMER_ID . ' = ?', $customerId);

        return (int)$connection->fetchOne($select);
    }

    /**
     * Whether the customer already has a pet with this name and species.
     *
     * Relies on the column's case-insensitive collation, so "Max" and "max" match.
     *
     * @param int $customerId
     * @param string $name
     * @param string $species
     * @param int|null $excludePetId The pet being updated, so it doesn't match itself
     * @return bool
     */
    public function hasPetNamed(int $customerId, string $name, string $species, ?int $excludePetId = null): bool
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), [PetInterface::PET_ID])
            ->where(PetInterface::CUSTOMER_ID . ' = ?', $customerId)
            ->where(PetInterface::NAME . ' = ?', $name)
            ->where(PetInterface::SPECIES . ' = ?', $species)
            ->limit(1);
        if ($excludePetId !== null) {
            $select->where(PetInterface::PET_ID . ' != ?', $excludePetId);
        }

        return (bool)$connection->fetchOne($select);
    }
}
