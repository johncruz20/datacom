<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Block\Adminhtml\Customer;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use PawsWhiskers\PetProfile\Api\CustomerPetManagementInterface;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Model\Source\Gender;
use PawsWhiskers\PetProfile\Model\Source\Species;

/**
 * Read-only "Pet Profiles" tab on the admin customer edit page.
 *
 * Rendered with the page rather than loaded by Ajax: a customer has at most a handful of pets
 * (see the per-customer limit), so a paged grid would add requests without adding value.
 */
class PetsTab extends Template implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'PawsWhiskers_PetProfile::customer/pets_tab.phtml';

    /**
     * @var PetInterface[]|null
     */
    private ?array $pets = null;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param CustomerPetManagementInterface $petManagement
     * @param Species $species
     * @param Gender $gender
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly CustomerPetManagementInterface $petManagement,
        private readonly Species $species,
        private readonly Gender $gender,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Customer being edited, or null on the "New Customer" page.
     *
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        $customerId = (int)$this->getRequest()->getParam('id');

        return $customerId > 0 ? $customerId : null;
    }

    /**
     * Pets owned by the customer being edited.
     *
     * @return PetInterface[]
     */
    public function getPets(): array
    {
        if ($this->pets === null) {
            $customerId = $this->getCustomerId();
            $this->pets = $customerId === null ? [] : $this->petManagement->getList($customerId);
        }

        return $this->pets;
    }

    /**
     * Human-readable species label.
     *
     * @param string|null $code
     * @return string
     */
    public function getSpeciesLabel(?string $code): string
    {
        return $this->getOptionLabel($this->species->toOptionArray(), $code);
    }

    /**
     * Human-readable gender label.
     *
     * @param string|null $code
     * @return string
     */
    public function getGenderLabel(?string $code): string
    {
        return $this->getOptionLabel($this->gender->toOptionArray(), $code);
    }

    /**
     * Age derived from the date of birth, e.g. "3 years" or "5 months".
     *
     * @param string|null $dateOfBirth
     * @return string
     */
    public function getAge(?string $dateOfBirth): string
    {
        if ($dateOfBirth === null) {
            return '';
        }

        $age = (new \DateTimeImmutable($dateOfBirth))->diff(new \DateTimeImmutable('today'));
        if ($age->y > 0) {
            return (string)__('%1 year(s)', $age->y);
        }

        return (string)__('%1 month(s)', $age->m);
    }

    /**
     * @inheritDoc
     */
    public function getTabLabel()
    {
        return __('Pet Profiles');
    }

    /**
     * @inheritDoc
     */
    public function getTabTitle()
    {
        return __('Pet Profiles');
    }

    /**
     * @inheritDoc
     */
    public function canShowTab()
    {
        return $this->getCustomerId() !== null;
    }

    /**
     * @inheritDoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Find an option label by value, falling back to the raw code.
     *
     * @param array $options
     * @param string|null $code
     * @return string
     */
    private function getOptionLabel(array $options, ?string $code): string
    {
        foreach ($options as $option) {
            if ($option['value'] === $code) {
                return (string)$option['label'];
            }
        }

        return (string)$code;
    }
}
