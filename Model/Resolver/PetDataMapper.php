<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\Resolver;

use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\Phrase;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;

/**
 * Converts between GraphQL shapes (uid, UPPER_CASE enums) and the service contract.
 */
class PetDataMapper
{
    private const ENUM_FIELDS = [PetInterface::SPECIES, PetInterface::GENDER];

    /**
     * Constructor.
     *
     * @param Uid $uid
     */
    public function __construct(
        private readonly Uid $uid
    ) {
    }

    /**
     * Convert a pet to the GraphQL Pet type.
     *
     * @param PetInterface $pet
     * @return array
     */
    public function toGraphQl(PetInterface $pet): array
    {
        return [
            'uid' => $this->uid->encode((string)$pet->getPetId()),
            'name' => $pet->getName(),
            'species' => strtoupper((string)$pet->getSpecies()),
            'breed' => $pet->getBreed(),
            'gender' => $pet->getGender() === null ? null : strtoupper($pet->getGender()),
            'date_of_birth' => $pet->getDateOfBirth(),
            'weight_kg' => $pet->getWeightKg(),
            'created_at' => $pet->getCreatedAt(),
            'updated_at' => $pet->getUpdatedAt(),
            'model' => $pet,
        ];
    }

    /**
     * Apply only the fields present in the input, so an update is a partial patch.
     *
     * @param PetInterface $pet
     * @param array $input
     * @return PetInterface
     */
    public function applyInput(PetInterface $pet, array $input): PetInterface
    {
        foreach ($input as $field => $value) {
            if (in_array($field, self::ENUM_FIELDS, true) && $value !== null) {
                $value = strtolower($value);
            }
            match ($field) {
                PetInterface::NAME => $pet->setName((string)$value),
                PetInterface::SPECIES => $pet->setSpecies((string)$value),
                PetInterface::BREED => $pet->setBreed($value === null ? null : trim($value)),
                PetInterface::GENDER => $pet->setGender($value),
                PetInterface::DATE_OF_BIRTH => $pet->setDateOfBirth($value),
                PetInterface::WEIGHT_KG => $pet->setWeightKg($value === null ? null : (float)$value),
                default => null,
            };
        }

        return $pet;
    }

    /**
     * Decode a GraphQL uid to the pet ID.
     *
     * @param string $uid
     * @return int
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlInputException
     */
    public function decodeUid(string $uid): int
    {
        return (int)$this->uid->decode($uid);
    }

    /**
     * Keep every validation error, not just the generic aggregate message.
     *
     * @param InputException $e
     * @return GraphQlInputException
     */
    public function toGraphQlException(InputException $e): GraphQlInputException
    {
        $graphQlException = new GraphQlInputException($this->toPhrase($e), $e);
        // The GraphQL ErrorHandler outputs only getErrors() when it is non-empty, so add every one.
        foreach ($e->getErrors() as $error) {
            $graphQlException->addError(new GraphQlInputException($this->toPhrase($error)));
        }

        return $graphQlException;
    }

    /**
     * Rebuild the translatable phrase of an exception.
     *
     * @param \Magento\Framework\Exception\LocalizedException $e
     * @return Phrase
     */
    private function toPhrase(\Magento\Framework\Exception\LocalizedException $e): Phrase
    {
        return new Phrase($e->getRawMessage(), $e->getParameters());
    }
}
