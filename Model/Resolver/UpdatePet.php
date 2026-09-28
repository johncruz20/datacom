<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\Resolver;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PawsWhiskers\PetProfile\Api\CustomerPetManagementInterface;

class UpdatePet implements ResolverInterface
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
        $petId = $this->mapper->decodeUid((string)$args['uid']);

        try {
            $pet = $this->petManagement->get($customerId, $petId);
            $pet = $this->petManagement->save($customerId, $this->mapper->applyInput($pet, $args['input'] ?? []));
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('Could not find a pet with uid "%1".', $args['uid']), $e);
        } catch (AlreadyExistsException $e) {
            throw new GraphQlAlreadyExistsException(__($e->getRawMessage(), $e->getParameters()), $e);
        } catch (InputException $e) {
            throw $this->mapper->toGraphQlException($e);
        }

        return ['pet' => $this->mapper->toGraphQl($pet)];
    }
}
