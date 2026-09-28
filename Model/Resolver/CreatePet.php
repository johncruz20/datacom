<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\Resolver;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PawsWhiskers\PetProfile\Api\CustomerPetManagementInterface;
use PawsWhiskers\PetProfile\Api\Data\PetInterfaceFactory;

class CreatePet implements ResolverInterface
{
    /**
     * Constructor.
     *
     * @param CustomerPetManagementInterface $petManagement
     * @param PetInterfaceFactory $petFactory
     * @param CustomerContext $customerContext
     * @param PetDataMapper $mapper
     */
    public function __construct(
        private readonly CustomerPetManagementInterface $petManagement,
        private readonly PetInterfaceFactory $petFactory,
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
        $pet = $this->mapper->applyInput($this->petFactory->create(), $args['input'] ?? []);

        try {
            $pet = $this->petManagement->save($customerId, $pet);
        } catch (AlreadyExistsException $e) {
            throw new GraphQlAlreadyExistsException(__($e->getRawMessage(), $e->getParameters()), $e);
        } catch (InputException $e) {
            throw $this->mapper->toGraphQlException($e);
        }

        return ['pet' => $this->mapper->toGraphQl($pet)];
    }
}
