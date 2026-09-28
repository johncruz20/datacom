<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model;

use Magento\Framework\Api\SearchResults;
use PawsWhiskers\PetProfile\Api\Data\PetSearchResultsInterface;

/**
 * Typed search results, so PetRepository::getList() honours its declared return type.
 */
class PetSearchResults extends SearchResults implements PetSearchResultsInterface
{
}
