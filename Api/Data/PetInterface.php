<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Pet profile belonging to a registered customer.
 *
 * @api
 */
interface PetInterface extends ExtensibleDataInterface
{
    public const PET_ID = 'pet_id';
    public const CUSTOMER_ID = 'customer_id';
    public const NAME = 'name';
    public const SPECIES = 'species';
    public const BREED = 'breed';
    public const GENDER = 'gender';
    public const DATE_OF_BIRTH = 'date_of_birth';
    public const WEIGHT_KG = 'weight_kg';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * Get pet ID.
     *
     * @return int|null
     */
    public function getPetId(): ?int;

    /**
     * Set pet ID.
     *
     * @param int|null $petId
     * @return $this
     */
    public function setPetId(?int $petId): self;

    /**
     * Get customer ID.
     *
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * Set customer ID.
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId): self;

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set name.
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * Species code, see \PawsWhiskers\PetProfile\Model\Source\Species.
     *
     * @return string|null
     */
    public function getSpecies(): ?string;

    /**
     * Set species.
     *
     * @param string $species
     * @return $this
     */
    public function setSpecies(string $species): self;

    /**
     * Get breed.
     *
     * @return string|null
     */
    public function getBreed(): ?string;

    /**
     * Set breed.
     *
     * @param string|null $breed
     * @return $this
     */
    public function setBreed(?string $breed): self;

    /**
     * Gender code, see \PawsWhiskers\PetProfile\Model\Source\Gender.
     *
     * @return string|null
     */
    public function getGender(): ?string;

    /**
     * Set gender.
     *
     * @param string|null $gender
     * @return $this
     */
    public function setGender(?string $gender): self;

    /**
     * Date of birth in Y-m-d format.
     *
     * @return string|null
     */
    public function getDateOfBirth(): ?string;

    /**
     * Set date of birth.
     *
     * @param string|null $dateOfBirth
     * @return $this
     */
    public function setDateOfBirth(?string $dateOfBirth): self;

    /**
     * Get weight (kg).
     *
     * @return float|null
     */
    public function getWeightKg(): ?float;

    /**
     * Set weight (kg).
     *
     * @param float|null $weightKg
     * @return $this
     */
    public function setWeightKg(?float $weightKg): self;

    /**
     * Get created at.
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Get updated at.
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Get extension attributes.
     *
     * @return \PawsWhiskers\PetProfile\Api\Data\PetExtensionInterface|null
     */
    public function getExtensionAttributes(): ?PetExtensionInterface;

    /**
     * Set extension attributes.
     *
     * @param \PawsWhiskers\PetProfile\Api\Data\PetExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(PetExtensionInterface $extensionAttributes): self;
}
