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
}
