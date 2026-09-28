<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Allowed species codes. Kept as a closed list (not free text) so the data is usable
 * for segmentation and recommendation rules without normalisation.
 */
class Species implements OptionSourceInterface
{
    public const DOG = 'dog';
    public const CAT = 'cat';
    public const BIRD = 'bird';
    public const FISH = 'fish';
    public const RABBIT = 'rabbit';
    public const SMALL_MAMMAL = 'small_mammal';
    public const REPTILE = 'reptile';
    public const OTHER = 'other';

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $labels = [
            self::DOG => __('Dog'),
            self::CAT => __('Cat'),
            self::BIRD => __('Bird'),
            self::FISH => __('Fish'),
            self::RABBIT => __('Rabbit'),
            self::SMALL_MAMMAL => __('Small Mammal'),
            self::REPTILE => __('Reptile'),
            self::OTHER => __('Other'),
        ];
        $options = [];
        foreach ($labels as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
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
