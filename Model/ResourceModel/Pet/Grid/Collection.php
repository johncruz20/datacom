<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Model\ResourceModel\Pet\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Admin grid collection: pets joined to their owner's email and name.
 *
 * Every column is mapped to a qualified name because customer_entity also has
 * created_at / updated_at, which would otherwise make filters and sorting ambiguous.
 */
class Collection extends SearchResult
{
    private const PET_COLUMNS = [
        'pet_id', 'customer_id', 'name', 'species', 'breed', 'gender',
        'date_of_birth', 'weight_kg', 'created_at', 'updated_at',
    ];

    /**
     * @inheritDoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $customerName = $this->getConnection()->getConcatSql(['customer.firstname', 'customer.lastname'], ' ');
        $this->getSelect()->joinLeft(
            ['customer' => $this->getTable('customer_entity')],
            'customer.entity_id = main_table.customer_id',
            ['customer_email' => 'customer.email', 'customer_name' => $customerName]
        );

        foreach (self::PET_COLUMNS as $column) {
            $this->addFilterToMap($column, 'main_table.' . $column);
        }
        $this->addFilterToMap('customer_email', 'customer.email');
        $this->addFilterToMap('customer_name', $customerName);

        return $this;
    }
}
