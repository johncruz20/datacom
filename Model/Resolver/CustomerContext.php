<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Resolves the authenticated customer ID from the GraphQL context.
 */
class CustomerContext
{
    /**
     * Get customer ID.
     *
     * @param mixed $context
     * @return int
     * @throws GraphQlAuthorizationException
     */
    public function getCustomerId($context): int
    {
        /** @var ContextInterface $context */
        if (!$context->getExtensionAttributes()->getIsCustomer() || !$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        return (int)$context->getUserId();
    }
}
