<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Api\PetRepositoryInterface;
use PawsWhiskers\PetProfile\Model\Config;
use PawsWhiskers\PetProfile\Model\CustomerPetManagement;
use PawsWhiskers\PetProfile\Model\ResourceModel\Pet as PetResource;
use PawsWhiskers\PetProfile\Model\Source\Species;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PawsWhiskers\PetProfile\Model\CustomerPetManagement
 */
class CustomerPetManagementTest extends TestCase
{
    private const CUSTOMER_ID = 42;
    private const OTHER_CUSTOMER_ID = 7;

    /**
     * @var PetRepositoryInterface&MockObject
     */
    private PetRepositoryInterface $petRepository;

    /**
     * @var PetResource&MockObject
     */
    private PetResource $petResource;

    /**
     * @var Config&MockObject
     */
    private Config $config;

    /**
     * @var CustomerPetManagement
     */
    private CustomerPetManagement $management;

    protected function setUp(): void
    {
        $this->petRepository = $this->createMock(PetRepositoryInterface::class);
        $this->petResource = $this->createMock(PetResource::class);
        $this->config = $this->createMock(Config::class);
        $this->config->method('getMaxPetsPerCustomer')->willReturn(10);

        $this->management = new CustomerPetManagement(
            $this->petRepository,
            $this->petResource,
            $this->createMock(SearchCriteriaBuilder::class),
            $this->createMock(SortOrderBuilder::class),
            $this->config,
            new Species()
        );
    }

    public function testCreateAssignsAuthenticatedCustomerRegardlessOfPayload(): void
    {
        $pet = $this->createPet(null, self::OTHER_CUSTOMER_ID);
        $this->petResource->method('countByCustomerId')->with(self::CUSTOMER_ID)->willReturn(3);

        $this->petRepository->expects($this->once())->method('save')->with($pet)->willReturn($pet);

        $this->assertSame($pet, $this->management->save(self::CUSTOMER_ID, $pet));
        $this->assertSame(self::CUSTOMER_ID, $pet->getCustomerId());
    }

    public function testCreateIsRejectedWhenPetLimitReached(): void
    {
        $this->petResource->method('countByCustomerId')->willReturn(10);
        $this->petRepository->expects($this->never())->method('save');

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('You can save up to 10 pets.');

        $this->management->save(self::CUSTOMER_ID, $this->createPet(null, null));
    }

    public function testLimitOfZeroDisablesTheCheck(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('getMaxPetsPerCustomer')->willReturn(0);
        $management = new CustomerPetManagement(
            $this->petRepository,
            $this->petResource,
            $this->createMock(SearchCriteriaBuilder::class),
            $this->createMock(SortOrderBuilder::class),
            $config,
            new Species()
        );
        $pet = $this->createPet(null, null);

        $this->petResource->expects($this->never())->method('countByCustomerId');
        $this->petRepository->expects($this->once())->method('save')->willReturn($pet);

        $management->save(self::CUSTOMER_ID, $pet);
    }

    public function testUpdateOfOwnPetSkipsLimitAndSaves(): void
    {
        $pet = $this->createPet(5, self::CUSTOMER_ID);
        $this->petRepository->method('getById')->with(5)->willReturn($pet);

        $this->petResource->expects($this->never())->method('countByCustomerId');
        $this->petRepository->expects($this->once())->method('save')->with($pet)->willReturn($pet);

        $this->management->save(self::CUSTOMER_ID, $pet);
    }

    public function testUpdateOfAnotherCustomersPetIsReportedAsNotFound(): void
    {
        $this->petRepository->method('getById')->with(5)->willReturn($this->createPet(5, self::OTHER_CUSTOMER_ID));
        $this->petRepository->expects($this->never())->method('save');

        $this->expectException(NoSuchEntityException::class);

        $this->management->save(self::CUSTOMER_ID, $this->createPet(5, self::CUSTOMER_ID));
    }

    public function testGetOfAnotherCustomersPetIsReportedAsNotFound(): void
    {
        $this->petRepository->method('getById')->willReturn($this->createPet(5, self::OTHER_CUSTOMER_ID));

        $this->expectException(NoSuchEntityException::class);

        $this->management->get(self::CUSTOMER_ID, 5);
    }

    public function testDeleteOfAnotherCustomersPetIsReportedAsNotFound(): void
    {
        $this->petRepository->method('getById')->willReturn($this->createPet(5, self::OTHER_CUSTOMER_ID));
        $this->petRepository->expects($this->never())->method('delete');

        $this->expectException(NoSuchEntityException::class);

        $this->management->delete(self::CUSTOMER_ID, 5);
    }

    public function testDeleteOfOwnPet(): void
    {
        $pet = $this->createPet(5, self::CUSTOMER_ID);
        $this->petRepository->method('getById')->willReturn($pet);
        $this->petRepository->expects($this->once())->method('delete')->with($pet)->willReturn(true);

        $this->assertTrue($this->management->delete(self::CUSTOMER_ID, 5));
    }

    public function testCreateOfDuplicatePetIsRejected(): void
    {
        $this->petResource->method('countByCustomerId')->willReturn(1);
        $this->petResource->expects($this->once())->method('hasPetNamed')
            ->with(self::CUSTOMER_ID, 'Grasya', Species::DOG, null)
            ->willReturn(true);
        $this->petRepository->expects($this->never())->method('save');

        $this->expectException(AlreadyExistsException::class);
        $this->expectExceptionMessage('You already have a dog named "Grasya".');

        $this->management->save(self::CUSTOMER_ID, $this->createPet(null, null, 'Grasya'));
    }

    public function testNameIsTrimmedBeforeDuplicateCheckAndSave(): void
    {
        $pet = $this->createPet(null, null, "  Grasya \t");
        $this->petResource->expects($this->once())->method('hasPetNamed')
            ->with(self::CUSTOMER_ID, 'Grasya', Species::DOG, null)
            ->willReturn(false);
        $this->petRepository->expects($this->once())->method('save')->willReturn($pet);

        $this->management->save(self::CUSTOMER_ID, $pet);

        $this->assertSame('Grasya', $pet->getName());
    }

    public function testRenameToAnotherOwnPetsNameIsRejectedButPetDoesNotMatchItself(): void
    {
        $pet = $this->createPet(5, self::CUSTOMER_ID, 'Tom', Species::CAT);
        $this->petRepository->method('getById')->with(5)->willReturn($pet);
        $this->petResource->expects($this->once())->method('hasPetNamed')
            ->with(self::CUSTOMER_ID, 'Tom', Species::CAT, 5)
            ->willReturn(true);
        $this->petRepository->expects($this->never())->method('save');

        $this->expectException(AlreadyExistsException::class);
        $this->expectExceptionMessage('You already have a cat named "Tom".');

        $this->management->save(self::CUSTOMER_ID, $pet);
    }

    /**
     * Pet mock that remembers its customer ID and name, so changes made by the service are visible.
     *
     * @param int|null $petId
     * @param int|null $customerId
     * @param string $name
     * @param string $species
     * @return PetInterface&MockObject
     */
    private function createPet(
        ?int $petId,
        ?int $customerId,
        string $name = 'Rex',
        string $species = Species::DOG
    ): PetInterface {
        $state = ['customer_id' => $customerId, 'name' => $name];
        $pet = $this->createMock(PetInterface::class);
        $pet->method('getPetId')->willReturn($petId);
        $pet->method('getSpecies')->willReturn($species);
        $pet->method('getCustomerId')->willReturnCallback(function () use (&$state) {
            return $state['customer_id'];
        });
        $pet->method('setCustomerId')->willReturnCallback(function (int $id) use (&$state, &$pet) {
            $state['customer_id'] = $id;
            return $pet;
        });
        $pet->method('getName')->willReturnCallback(function () use (&$state) {
            return $state['name'];
        });
        $pet->method('setName')->willReturnCallback(function (string $value) use (&$state, &$pet) {
            $state['name'] = $value;
            return $pet;
        });

        return $pet;
    }
}
