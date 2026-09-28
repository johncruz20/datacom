<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Api\PetRepositoryInterface;
use PawsWhiskers\PetProfile\Model\Config;
use PawsWhiskers\PetProfile\Model\CustomerPetManagement;
use PawsWhiskers\PetProfile\Model\ResourceModel\Pet as PetResource;
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
            $this->config
        );
    }

    public function testCreateAssignsAuthenticatedCustomerRegardlessOfPayload(): void
    {
        $pet = $this->createPet(null, self::OTHER_CUSTOMER_ID);
        $this->petResource->method('countByCustomerId')->with(self::CUSTOMER_ID)->willReturn(3);

        $pet->expects($this->once())->method('setCustomerId')->with(self::CUSTOMER_ID);
        $this->petRepository->expects($this->once())->method('save')->with($pet)->willReturn($pet);

        $this->assertSame($pet, $this->management->save(self::CUSTOMER_ID, $pet));
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
            $config
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

    /**
     * Create pet.
     *
     * @param int|null $petId
     * @param int|null $customerId
     * @return PetInterface&MockObject
     */
    private function createPet(?int $petId, ?int $customerId): PetInterface
    {
        $pet = $this->createMock(PetInterface::class);
        $pet->method('getPetId')->willReturn($petId);
        $pet->method('getCustomerId')->willReturn($customerId);

        return $pet;
    }
}
