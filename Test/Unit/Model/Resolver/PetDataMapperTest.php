<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Test\Unit\Model\Resolver;

use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Query\Uid;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Model\Resolver\PetDataMapper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PawsWhiskers\PetProfile\Model\Resolver\PetDataMapper
 */
class PetDataMapperTest extends TestCase
{
    /**
     * @var PetDataMapper
     */
    private PetDataMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new PetDataMapper(new Uid());
    }

    public function testEveryValidationErrorBecomesAGraphQlError(): void
    {
        $input = new InputException();
        $input->addError(__('"%1" is required.', 'name'));
        $input->addError(__('"%1" is not a valid species.', 'dragon'));

        $errors = $this->mapper->toGraphQlException($input)->getErrors();

        $this->assertSame(
            ['"name" is required.', '"dragon" is not a valid species.'],
            array_map(fn ($error) => $error->getMessage(), $errors)
        );
    }

    public function testSingleInputExceptionIsKept(): void
    {
        $exception = $this->mapper->toGraphQlException(new InputException(__('You can save up to %1 pets.', 10)));

        $this->assertSame('You can save up to 10 pets.', $exception->getMessage());
    }

    public function testInputEnumsAreLowerCasedAndOnlySentFieldsApplied(): void
    {
        $pet = $this->createMock(PetInterface::class);
        $pet->expects($this->once())->method('setSpecies')->with('small_mammal');
        $pet->expects($this->once())->method('setBreed')->with(null);
        $pet->expects($this->never())->method('setName');
        $pet->expects($this->never())->method('setGender');

        $this->mapper->applyInput($pet, ['species' => 'SMALL_MAMMAL', 'breed' => null]);
    }

    public function testOutputUsesUidAndUpperCaseEnums(): void
    {
        $pet = $this->createMock(PetInterface::class);
        $pet->method('getPetId')->willReturn(12);
        $pet->method('getSpecies')->willReturn('cat');
        $pet->method('getGender')->willReturn(null);

        $data = $this->mapper->toGraphQl($pet);

        $this->assertSame(base64_encode('12'), $data['uid']);
        $this->assertSame('CAT', $data['species']);
        $this->assertNull($data['gender']);
    }
}
