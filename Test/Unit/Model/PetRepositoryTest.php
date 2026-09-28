<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use PawsWhiskers\PetProfile\Api\Data\PetSearchResultsInterfaceFactory;
use PawsWhiskers\PetProfile\Model\Pet;
use PawsWhiskers\PetProfile\Model\PetFactory;
use PawsWhiskers\PetProfile\Model\PetRepository;
use PawsWhiskers\PetProfile\Model\PetValidator;
use PawsWhiskers\PetProfile\Model\ResourceModel\Pet as PetResource;
use PawsWhiskers\PetProfile\Model\ResourceModel\Pet\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PawsWhiskers\PetProfile\Model\PetRepository
 */
class PetRepositoryTest extends TestCase
{
    /**
     * @var PetResource&MockObject
     */
    private PetResource $resource;

    /**
     * @var PetRepository
     */
    private PetRepository $repository;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(PetResource::class);
        $this->repository = new PetRepository(
            $this->resource,
            $this->createMock(PetFactory::class),
            $this->createMock(CollectionFactory::class),
            $this->createMock(PetSearchResultsInterfaceFactory::class),
            $this->createMock(CollectionProcessorInterface::class),
            $this->createMock(PetValidator::class)
        );
    }

    public function testUniqueKeyViolationIsReportedAsDuplicateNotGenericFailure(): void
    {
        $pet = $this->createMock(Pet::class);
        $pet->method('getName')->willReturn('Grasya');
        $this->resource->method('save')
            ->willThrowException(new AlreadyExistsException(__('Unique constraint violation found')));

        $this->expectException(AlreadyExistsException::class);
        $this->expectExceptionMessage('A pet named "Grasya" of this species already exists for this customer.');

        $this->repository->save($pet);
    }

    public function testOtherDatabaseErrorsAreWrapped(): void
    {
        $this->resource->method('save')->willThrowException(new \RuntimeException('Deadlock'));

        $this->expectException(CouldNotSaveException::class);

        $this->repository->save($this->createMock(Pet::class));
    }
}
