<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Test\Unit\Model\Template;

use Graycore\StyleSmugglerPatch\Model\Template\BlockDirectiveAllowList;
use PHPUnit\Framework\TestCase;

class BlockDirectiveAllowListTest extends TestCase
{
    public function testAnEmptyListAllowsNothing()
    {
        $allowList = new BlockDirectiveAllowList();

        $this->assertFalse($allowList->isAllowed('Magento\Framework\View\Element\Template'));
        $this->assertFalse($allowList->isAllowed('Magento\Cms\Block\Block'));
    }

    public function testAnAllowedClassIsAllowed()
    {
        $allowList = new BlockDirectiveAllowList(['Magento\Cms\Block\Block']);

        $this->assertTrue($allowList->isAllowed('Magento\Cms\Block\Block'));
    }

    public function testAnUnlistedClassIsRefused()
    {
        $allowList = new BlockDirectiveAllowList(['Magento\Cms\Block\Block']);

        $this->assertFalse($allowList->isAllowed('Magento\Framework\View\Element\Text'));
    }

    public function testMatchingIgnoresCaseAsPhpDoes()
    {
        $allowList = new BlockDirectiveAllowList(['Magento\Cms\Block\Block']);

        $this->assertTrue($allowList->isAllowed('magento\cms\block\block'));
        $this->assertTrue($allowList->isAllowed('MAGENTO\CMS\BLOCK\BLOCK'));
    }

    public function testMatchingIgnoresSeparatorSpelling()
    {
        $allowList = new BlockDirectiveAllowList(['Magento\Cms\Block\Block']);

        $this->assertTrue($allowList->isAllowed('\Magento\Cms\Block\Block'));
        $this->assertTrue($allowList->isAllowed('Magento\\\\Cms\\\\Block\\\\Block'));
        $this->assertTrue($allowList->isAllowed('Magento/Cms/Block/Block'));
        $this->assertTrue($allowList->isAllowed('  Magento\Cms\Block\Block  '));
    }

    public function testTheSameSpellingsNormalizeOnTheWayIn()
    {
        $allowList = new BlockDirectiveAllowList(['/magento/cms/block/block']);

        $this->assertTrue($allowList->isAllowed('Magento\Cms\Block\Block'));
    }

    public function testASubclassOfAnAllowedClassIsNotItselfAllowed()
    {
        $allowList = new BlockDirectiveAllowList(['Magento\Framework\View\Element\Template']);

        $this->assertFalse($allowList->isAllowed('Magento\Framework\View\Element\Template\Evil'));
        $this->assertFalse($allowList->isAllowed('Vendor\Module\Block\Evil'));
    }

    public function testAnEmptyClassNameIsRefused()
    {
        $allowList = new BlockDirectiveAllowList(['Magento\Cms\Block\Block', '', '   ']);

        $this->assertFalse($allowList->isAllowed(''));
        $this->assertFalse($allowList->isAllowed('   '));
        $this->assertFalse($allowList->isAllowed('\\'));
    }

    public function testNonStringEntriesAreIgnored()
    {
        $allowList = new BlockDirectiveAllowList([null, 42, ['Magento\Cms\Block\Block']]);

        $this->assertFalse($allowList->isAllowed('Magento\Cms\Block\Block'));
    }

    public function testItAlwaysRefusesBackendBlocks()
    {
        $refused = [
            'backend block' => 'Magento\\Backend\\Block\\Widget\\Grid',
            'the style smuggler column set' => 'Magento\\Backend\\Block\\Widget\\Grid\\ColumnSet',
            'backend block, leading slash' => '\\Magento\\Backend\\Block\\Widget\\Grid',
            'backend block, forward slashes' => 'Magento/Backend/Block/Widget/Grid',
            'backend block, surrounding space' => '  Magento\\Backend\\Block\\Widget\\Grid  ',
            'backend block, mixed case' => 'magento\\backend\\block\\Widget\\Grid',
            'third party adminhtml block' => 'Vendor\\Module\\Block\\Adminhtml\\Something',
            'core adminhtml block' => 'Magento\\Sales\\Block\\Adminhtml\\Order\\Grid',
            'adminhtml block, mixed case' => 'Vendor\\Module\\block\\adminhtml\\Something',
            'email template preview block' => 'Magento\\Email\\Block\\Adminhtml\\Template\\Preview',
            'doubled separators' => 'Magento\\\\Backend\\\\Block\\\\Widget\\\\Grid',
            'doubled separators, adminhtml' => 'Vendor\\\\Module\\\\Block\\\\\\\\Adminhtml\\\\Something',
            'mixed doubled and forward separators' => 'Magento//Backend\\\\Block/Widget\\Grid',
            'many leading separators' => '\\\\\\\\Magento\\Backend\\Block\\Widget\\Grid',
        ];

        $allowList = new BlockDirectiveAllowList(array_values($refused));

        foreach ($refused as $description => $class) {
            $this->assertTrue(
                $allowList->isAlwaysRefused($class),
                $description . ' should always be refused'
            );
            $this->assertFalse(
                $allowList->isAllowed($class),
                $description . ' should not be allowed even when it is on the list'
            );
        }
    }

    public function testItDoesNotMistakeAnOrdinaryBlockForABackendOne()
    {
        $ordinary = [
            'core frontend block' => 'Magento\\Catalog\\Block\\Product\\View',
            'cms block' => 'Magento\\Cms\\Block\\Block',
            'third party frontend block' => 'Vendor\\Module\\Block\\Product\\ListProduct',
            'empty string' => '',
            'whitespace only' => '   ',
            'adminhtml in the class name but not the namespace' => 'Vendor\\Module\\Block\\AdminhtmlNotice',
            'backend in a different namespace' => 'Vendor\\Backend\\Block\\Something',
            'backend model rather than block' => 'Magento\\Backend\\Model\\Url',
        ];

        $allowList = new BlockDirectiveAllowList();

        foreach ($ordinary as $description => $class) {
            $this->assertFalse(
                $allowList->isAlwaysRefused($class),
                $description . ' should not be treated as a backend block'
            );
        }
    }
}
