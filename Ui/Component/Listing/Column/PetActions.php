<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Row action linking a pet to its owner's customer page.
 */
class PetActions extends Column
{
    /**
     * Constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Add the "View Customer" link to each row.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        // Iterate the array itself by reference: "?? []" here would modify a temporary copy.
        foreach ($dataSource['data']['items'] as &$item) {
            if (empty($item['customer_id'])) {
                continue;
            }
            $item[$this->getData('name')]['view_customer'] = [
                'href' => $this->urlBuilder->getUrl('customer/index/edit', ['id' => $item['customer_id']]),
                'label' => __('View Customer'),
            ];
        }
        unset($item);

        return $dataSource;
    }
}
