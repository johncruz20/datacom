<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface PetSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return \PawsWhiskers\PetProfile\Api\Data\PetInterface[]
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \PawsWhiskers\PetProfile\Api\Data\PetInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
