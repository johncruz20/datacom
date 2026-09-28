<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use PawsWhiskers\PetProfile\Ui\Component\Listing\Column\PetActions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PawsWhiskers\PetProfile\Ui\Component\Listing\Column\PetActions
 */
class PetActionsTest extends TestCase
{
    public function testAddsViewCustomerLinkToEveryRowWithAnOwner(): void
    {
        $context = $this->createMock(ContextInterface::class);
        $context->method('getProcessor')->willReturn($this->createMock(Processor::class));
        $urlBuilder = $this->createMock(UrlInterface::class);
        $urlBuilder->method('getUrl')->willReturnCallback(
            fn (string $route, array $params) => $route . '/id/' . $params['id']
        );
        $column = new PetActions(
            $context,
            $this->createMock(UiComponentFactory::class),
            $urlBuilder,
            [],
            ['name' => 'actions']
        );

        $result = $column->prepareDataSource(['data' => ['items' => [
            ['pet_id' => 1, 'customer_id' => 4],
            ['pet_id' => 2, 'customer_id' => null],
        ]]]);

        $items = $result['data']['items'];
        $this->assertSame('customer/index/edit/id/4', $items[0]['actions']['view_customer']['href']);
        $this->assertArrayNotHasKey('actions', $items[1]);
    }

    public function testDataSourceWithoutItemsIsReturnedUnchanged(): void
    {
        $context = $this->createMock(ContextInterface::class);
        $context->method('getProcessor')->willReturn($this->createMock(Processor::class));
        $column = new PetActions(
            $context,
            $this->createMock(UiComponentFactory::class),
            $this->createMock(UrlInterface::class)
        );

        $dataSource = ['data' => ['totalRecords' => 0]];

        $this->assertSame($dataSource, $column->prepareDataSource($dataSource));
    }
}
