<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Test\Unit\Model;

use Magento\Framework\Exception\InputException;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Model\PetValidator;
use PawsWhiskers\PetProfile\Model\Source\Gender;
use PawsWhiskers\PetProfile\Model\Source\Species;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PawsWhiskers\PetProfile\Model\PetValidator
 */
class PetValidatorTest extends TestCase
{
    /**
     * @var PetValidator
     */
    private PetValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PetValidator(new Species(), new Gender());
    }

    public function testValidPetPasses(): void
    {
        $this->validator->validate($this->createPet([
            'name' => 'Biscuit',
            'species' => Species::DOG,
            'breed' => 'Beagle',
            'gender' => Gender::FEMALE,
            'date_of_birth' => '2021-04-02',
            'weight_kg' => 11.5,
        ]));

        $this->addToAssertionCount(1);
    }

    public function testOnlyRequiredFieldsPass(): void
    {
        $this->validator->validate($this->createPet(['name' => 'Tom', 'species' => Species::CAT]));

        $this->addToAssertionCount(1);
    }

    /**
     * Test invalid pet is rejected.
     *
     * @dataProvider invalidPetProvider
     * @param array $data
     * @param string $expectedError
     */
    public function testInvalidPetIsRejected(array $data, string $expectedError): void
    {
        $messages = $this->collectErrors($this->createPet($data + ['name' => 'Rex', 'species' => Species::DOG]));

        $this->assertCount(1, $messages);
        $this->assertStringContainsString($expectedError, $messages[0]);
    }

    /**
     * Invalid pet provider.
     *
     * @return array
     */
    public static function invalidPetProvider(): array
    {
        return [
            'blank name' => [['name' => '   '], '"name" is required'],
            'name too long' => [['name' => str_repeat('a', 65)], '"name" must be 64 characters or fewer'],
            'unknown species' => [['species' => 'dragon'], '"dragon" is not a valid species'],
            'species is case sensitive' => [['species' => 'DOG'], 'is not a valid species'],
            'breed too long' => [['breed' => str_repeat('b', 129)], '"breed" must be 128 characters or fewer'],
            'unknown gender' => [['gender' => 'other'], '"other" is not a valid gender'],
            'bad date format' => [['date_of_birth' => '02/04/2021'], 'valid date in YYYY-MM-DD format'],
            'impossible date' => [['date_of_birth' => '2021-02-30'], 'valid date in YYYY-MM-DD format'],
            'implausibly old' => [['date_of_birth' => '1960-01-01'], 'is more than 50 years ago'],
            'zero weight' => [['weight_kg' => 0.0], '"weight_kg" must be greater than 0'],
            'too heavy' => [['weight_kg' => 150.01], '"weight_kg" must be greater than 0'],
        ];
    }

    public function testDateOfBirthInTheFutureIsRejected(): void
    {
        $tomorrow = (new \DateTimeImmutable('tomorrow'))->format('Y-m-d');

        $messages = $this->collectErrors(
            $this->createPet(['name' => 'Rex', 'species' => Species::DOG, 'date_of_birth' => $tomorrow])
        );

        $this->assertSame(['"date_of_birth" cannot be in the future.'], $messages);
    }

    public function testAllErrorsAreReportedTogether(): void
    {
        $messages = $this->collectErrors(
            $this->createPet(['name' => '', 'species' => 'unicorn', 'weight_kg' => -1.0])
        );

        $this->assertCount(3, $messages);
    }

    /**
     * Collect errors.
     *
     * @param PetInterface $pet
     * @return string[]
     */
    private function collectErrors(PetInterface $pet): array
    {
        try {
            $this->validator->validate($pet);
        } catch (InputException $e) {
            return array_map(fn ($error) => $error->getMessage(), $e->getErrors() ?: [$e]);
        }
        $this->fail('Expected an InputException.');
    }

    /**
     * Create pet.
     *
     * @param array $data
     * @return PetInterface&MockObject
     */
    private function createPet(array $data): PetInterface
    {
        $pet = $this->createMock(PetInterface::class);
        $pet->method('getName')->willReturn($data['name'] ?? null);
        $pet->method('getSpecies')->willReturn($data['species'] ?? null);
        $pet->method('getBreed')->willReturn($data['breed'] ?? null);
        $pet->method('getGender')->willReturn($data['gender'] ?? null);
        $pet->method('getDateOfBirth')->willReturn($data['date_of_birth'] ?? null);
        $pet->method('getWeightKg')->willReturn($data['weight_kg'] ?? null);

        return $pet;
    }
}
