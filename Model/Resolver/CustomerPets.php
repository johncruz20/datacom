<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PawsWhiskers\PetProfile\Api\CustomerPetManagementInterface;

/**
 * Customer.pets. The Customer query is already private (not cached by Varnish/FPC).
 */
class CustomerPets implements ResolverInterface
{
    /**
     * Constructor.
     *
     * @param CustomerPetManagementInterface $petManagement
     * @param CustomerContext $customerContext
     * @param PetDataMapper $mapper
     */
    public function __construct(
        private readonly CustomerPetManagementInterface $petManagement,
        private readonly CustomerContext $customerContext,
        private readonly PetDataMapper $mapper
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $customerId = $this->customerContext->getCustomerId($context);

        return array_map(
            fn ($pet) => $this->mapper->toGraphQl($pet),
            $this->petManagement->getList($customerId)
        );
    }
}
