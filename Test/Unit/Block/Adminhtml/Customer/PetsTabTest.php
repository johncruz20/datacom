<?php
declare(strict_types=1);

namespace PawsWhiskers\PetProfile\Test\Unit\Block\Adminhtml\Customer;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\RequestInterface;
use PawsWhiskers\PetProfile\Api\CustomerPetManagementInterface;
use PawsWhiskers\PetProfile\Api\Data\PetInterface;
use PawsWhiskers\PetProfile\Block\Adminhtml\Customer\PetsTab;
use PawsWhiskers\PetProfile\Model\Source\Gender;
use PawsWhiskers\PetProfile\Model\Source\Species;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PawsWhiskers\PetProfile\Block\Adminhtml\Customer\PetsTab
 */
class PetsTabTest extends TestCase
{
    /**
     * @var CustomerPetManagementInterface&MockObject
     */
    private CustomerPetManagementInterface $petManagement;

    protected function setUp(): void
    {
        $this->petManagement = $this->createMock(CustomerPetManagementInterface::class);
    }

    public function testTabIsHiddenOnNewCustomerPageAndLoadsNothing(): void
    {
        $this->petManagement->expects($this->never())->method('getList');
        $tab = $this->createTab(null);

        $this->assertFalse($tab->canShowTab());
        $this->assertSame([], $tab->getPets());
    }

    public function testLoadsPetsOfCustomerBeingEditedOnce(): void
    {
        $pet = $this->createMock(PetInterface::class);
        $this->petManagement->expects($this->once())->method('getList')->with(8)->willReturn([$pet]);
        $tab = $this->createTab('8');

        $this->assertTrue($tab->canShowTab());
        $this->assertSame([$pet], $tab->getPets());
        $this->assertSame([$pet], $tab->getPets());
    }

    public function testLabelsFallBackToRawCode(): void
    {
        $tab = $this->createTab('8');

        $this->assertSame('Small Mammal', $tab->getSpeciesLabel(Species::SMALL_MAMMAL));
        $this->assertSame('Female', $tab->getGenderLabel(Gender::FEMALE));
        $this->assertSame('', $tab->getGenderLabel(null));
        $this->assertSame('legacy_code', $tab->getSpeciesLabel('legacy_code'));
    }

    public function testAgeIsShownInYearsOrMonths(): void
    {
        $tab = $this->createTab('8');
        $today = new \DateTimeImmutable('today');

        $this->assertSame('3 year(s)', $tab->getAge($today->modify('-3 years')->format('Y-m-d')));
        $this->assertSame('5 month(s)', $tab->getAge($today->modify('-5 months')->format('Y-m-d')));
        $this->assertSame('', $tab->getAge(null));
    }

    /**
     * @param string|null $customerIdParam
     * @return PetsTab
     */
    private function createTab(?string $customerIdParam): PetsTab
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('getParam')->with('id')->willReturn($customerIdParam);
        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($request);

        return new PetsTab($context, $this->petManagement, new Species(), new Gender());
    }
}
