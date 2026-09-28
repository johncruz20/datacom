<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use PawsWhiskers\PetProfile\Api\Data\PetExtensionInterface;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Model\ResourceModel\Pet as PetResource;

/**
 * Pet profile model.
 *
 * The event prefix gives us "paws_pet_profile_save_commit_after" / "..._delete_commit_after",
 * which is where the marketing sync is published: only once the DB transaction has committed.
 */
class Pet extends AbstractExtensibleModel implements PetInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'paws_pet_profile';

    /**
     * @var string
     */
    protected $_eventObject = 'pet';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(PetResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getPetId(): ?int
    {
        $value = $this->getData(self::PET_ID);
        return $value === null ? null : (int)$value;
    }

    /**
     * @inheritDoc
     */
    public function setPetId(?int $petId): PetInterface
    {
        return $this->setData(self::PET_ID, $petId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): ?int
    {
        $value = $this->getData(self::CUSTOMER_ID);
        return $value === null ? null : (int)$value;
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId): PetInterface
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): PetInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getSpecies(): ?string
    {
        return $this->getData(self::SPECIES);
    }

    /**
     * @inheritDoc
     */
    public function setSpecies(string $species): PetInterface
    {
        return $this->setData(self::SPECIES, $species);
    }

    /**
     * @inheritDoc
     */
    public function getBreed(): ?string
    {
        return $this->getData(self::BREED);
    }

    /**
     * @inheritDoc
     */
    public function setBreed(?string $breed): PetInterface
    {
        return $this->setData(self::BREED, $breed);
    }

    /**
     * @inheritDoc
     */
    public function getGender(): ?string
    {
        return $this->getData(self::GENDER);
    }

    /**
     * @inheritDoc
     */
    public function setGender(?string $gender): PetInterface
    {
        return $this->setData(self::GENDER, $gender);
    }

    /**
     * @inheritDoc
     */
    public function getDateOfBirth(): ?string
    {
        return $this->getData(self::DATE_OF_BIRTH);
    }

    /**
     * @inheritDoc
     */
    public function setDateOfBirth(?string $dateOfBirth): PetInterface
    {
        return $this->setData(self::DATE_OF_BIRTH, $dateOfBirth);
    }

    /**
     * @inheritDoc
     */
    public function getWeightKg(): ?float
    {
        $value = $this->getData(self::WEIGHT_KG);
        return $value === null ? null : (float)$value;
    }

    /**
     * @inheritDoc
     */
    public function setWeightKg(?float $weightKg): PetInterface
    {
        return $this->setData(self::WEIGHT_KG, $weightKg);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?PetExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(PetExtensionInterface $extensionAttributes): PetInterface
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
