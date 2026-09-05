<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Test\Unit\Model\Template;

use Graycore\StyleSmugglerPatch\Model\Template\Filter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    /**
     * Reach the private guard without building the filter's very large dependency graph.
     *
     * @param string $class
     * @return bool
     */
    private function isRestricted(string $class): bool
    {
        $filter = (new \ReflectionClass(Filter::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(Filter::class, 'isRestrictedBlockClass');
        return $method->invoke($filter, $class);
    }

    public function testItRestrictsBackendBlocks()
    {
        $restricted = [
            'backend block' => 'Magento\Backend\Block\Widget\Grid',
            'backend block, leading slash' => '\Magento\Backend\Block\Widget\Grid',
            'backend block, forward slashes' => 'Magento/Backend/Block/Widget/Grid',
            'backend block, surrounding space' => '  Magento\Backend\Block\Widget\Grid  ',
            'backend block, mixed case' => 'magento\backend\block\Widget\Grid',
            'third party adminhtml block' => 'Vendor\Module\Block\Adminhtml\Something',
            'core adminhtml block' => 'Magento\Sales\Block\Adminhtml\Order\Grid',
            'adminhtml block, mixed case' => 'Vendor\Module\block\adminhtml\Something',
            'email template preview block' => 'Magento\Email\Block\Adminhtml\Template\Preview',
            'doubled separators' => 'Magento\\\\Backend\\\\Block\\\\Widget\\\\Grid',
            'doubled separators, adminhtml' => 'Vendor\\\\Module\\\\Block\\\\\\\\Adminhtml\\\\Something',
            'mixed doubled and forward separators' => 'Magento//Backend\\\\Block/Widget\\Grid',
            'many leading separators' => '\\\\\\\\Magento\\Backend\\Block\\Widget\\Grid',
        ];

        foreach ($restricted as $description => $class) {
            $this->assertTrue($this->isRestricted($class), $description . ' should be restricted');
        }
    }

    public function testItAllowsOrdinaryBlocks()
    {
        $allowed = [
            'core frontend block' => 'Magento\Catalog\Block\Product\View',
            'cms block' => 'Magento\Cms\Block\Block',
            'third party frontend block' => 'Vendor\Module\Block\Product\ListProduct',
            'empty string' => '',
            'whitespace only' => '   ',
            'adminhtml in the class name but not the namespace' => 'Vendor\Module\Block\AdminhtmlNotice',
            'backend in a different namespace' => 'Vendor\Backend\Block\Something',
        ];

        foreach ($allowed as $description => $class) {
            $this->assertFalse($this->isRestricted($class), $description . ' should be allowed');
        }
    }
}
