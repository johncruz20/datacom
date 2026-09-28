<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model;

use Magento\Framework\Exception\InputException;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Model\Source\Gender;
use PawsWhiskers\PetProfile\Model\Source\Species;

/**
 * Business validation for a pet profile. Collects every error so API clients can
 * show them all at once instead of fixing fields one round-trip at a time.
 */
class PetValidator
{
    public const NAME_MAX_LENGTH = 64;
    public const BREED_MAX_LENGTH = 128;
    public const MAX_WEIGHT_KG = 150.0;
    public const MAX_AGE_YEARS = 50;

    /**
     * Constructor.
     *
     * @param Species $species
     * @param Gender $gender
     */
    public function __construct(
        private readonly Species $species,
        private readonly Gender $gender
    ) {
    }

    /**
     * Validate business rules for a pet, throwing one exception that lists every error.
     *
     * @param PetInterface $pet
     * @return void
     * @throws InputException
     */
    public function validate(PetInterface $pet): void
    {
        $exception = new InputException();

        $name = trim((string)$pet->getName());
        if ($name === '') {
            $exception->addError(__('"%1" is required.', 'name'));
        } elseif (mb_strlen($name) > self::NAME_MAX_LENGTH) {
            $exception->addError(__('"%1" must be %2 characters or fewer.', 'name', self::NAME_MAX_LENGTH));
        }

        if (!in_array($pet->getSpecies(), $this->species->getCodes(), true)) {
            $exception->addError(__('"%1" is not a valid species.', (string)$pet->getSpecies()));
        }

        if ($pet->getBreed() !== null && mb_strlen($pet->getBreed()) > self::BREED_MAX_LENGTH) {
            $exception->addError(__('"%1" must be %2 characters or fewer.', 'breed', self::BREED_MAX_LENGTH));
        }

        if ($pet->getGender() !== null && !in_array($pet->getGender(), $this->gender->getCodes(), true)) {
            $exception->addError(__('"%1" is not a valid gender.', $pet->getGender()));
        }

        if ($pet->getDateOfBirth() !== null) {
            $this->validateDateOfBirth($pet->getDateOfBirth(), $exception);
        }

        $weight = $pet->getWeightKg();
        if ($weight !== null && ($weight <= 0 || $weight > self::MAX_WEIGHT_KG)) {
            $exception->addError(__('"%1" must be greater than 0 and at most %2.', 'weight_kg', self::MAX_WEIGHT_KG));
        }

        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }

    /**
     * Validate date of birth.
     *
     * @param string $value
     * @param InputException $exception
     * @return void
     */
    private function validateDateOfBirth(string $value, InputException $exception): void
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            $exception->addError(__('"%1" must be a valid date in YYYY-MM-DD format.', 'date_of_birth'));
            return;
        }

        $today = new \DateTimeImmutable('today');
        if ($date > $today) {
            $exception->addError(__('"%1" cannot be in the future.', 'date_of_birth'));
        } elseif ($date < $today->modify('-' . self::MAX_AGE_YEARS . ' years')) {
            $exception->addError(__('"%1" is more than %2 years ago.', 'date_of_birth', self::MAX_AGE_YEARS));
        }
    }
}
