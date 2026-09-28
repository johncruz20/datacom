<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\ResourceModel\Pet;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PawsWhiskers\PetProfile\Model\Pet;
use PawsWhiskers\PetProfile\Model\ResourceModel\Pet as PetResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'pet_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(Pet::class, PetResource::class);
    }
}
