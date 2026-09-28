<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Gender implements OptionSourceInterface
{
    public const MALE = 'male';
    public const FEMALE = 'female';
    public const UNKNOWN = 'unknown';

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::MALE, 'label' => __('Male')],
            ['value' => self::FEMALE, 'label' => __('Female')],
            ['value' => self::UNKNOWN, 'label' => __('Unknown')],
        ];
    }

    /**
     * Get codes.
     *
     * @return string[]
     */
    public function getCodes(): array
    {
        return array_column($this->toOptionArray(), 'value');
    }
}
