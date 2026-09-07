<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Test\Unit\Plugin\Email\Model\Template;

use Graycore\StyleSmugglerPatch\Model\Template\BlockDirectiveAllowList;
use Graycore\StyleSmugglerPatch\Plugin\Email\Model\Template\ValidateBlockDirectiveClass;
use Magento\Email\Model\Template\Filter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ValidateBlockDirectiveClassTest extends TestCase
{
    /**
     * @var Filter
     */
    private $subject;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var callable
     */
    private $proceed;

    protected function setUp(): void
    {
        $this->subject = $this->createStub(Filter::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->proceed = function () {
            return 'RENDERED';
        };
    }

    /**
     * @param string[] $allowedClasses
     * @return ValidateBlockDirectiveClass
     */
    private function plugin(array $allowedClasses = [])
    {
        return new ValidateBlockDirectiveClass(
            new BlockDirectiveAllowList($allowedClasses),
            $this->logger
        );
    }

    /**
     * @param string $parameters
     * @return array
     */
    private function construction(string $parameters)
    {
        return ['{{block ' . $parameters . '}}', 'block', $parameters];
    }

    public function testItRefusesAClassThatIsNotAllowlisted()
    {
        $this->logger->expects($this->once())
            ->method('critical')
            ->with(
                $this->stringContains('not on the allowlist'),
                ['class' => 'Magento\Framework\View\Element\Text']
            );

        $result = $this->plugin()->aroundBlockDirective(
            $this->subject,
            $this->proceed,
            $this->construction('class="Magento\Framework\View\Element\Text"')
        );

        $this->assertSame('', $result);
    }

    public function testItRendersAnAllowlistedClass()
    {
        $this->logger->expects($this->never())->method('critical');

        $result = $this->plugin(['Magento\Framework\View\Element\Text'])->aroundBlockDirective(
            $this->subject,
            $this->proceed,
            $this->construction('class="Magento\Framework\View\Element\Text" text="hi"')
        );

        $this->assertSame('RENDERED', $result);
    }

    public function testItRefusesTheStyleSmugglerColumnSet()
    {
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->stringContains('cannot be allowlisted'), $this->anything());

        $result = $this->plugin(['Magento\Backend\Block\Widget\Grid\ColumnSet'])->aroundBlockDirective(
            $this->subject,
            $this->proceed,
            $this->construction(
                'class="Magento\Backend\Block\Widget\Grid\ColumnSet" rowUrl=$this.template_styles'
            )
        );

        $this->assertSame('', $result);
    }

    public function testItLeavesADirectiveWithNoClassParameterAlone()
    {
        $this->logger->expects($this->never())->method('critical');

        $result = $this->plugin()->aroundBlockDirective(
            $this->subject,
            $this->proceed,
            $this->construction('id="footer_links"')
        );

        $this->assertSame('RENDERED', $result);
    }

    public function testItRefusesAnEmptyClassParameter()
    {
        $this->logger->expects($this->once())->method('critical');

        $result = $this->plugin()->aroundBlockDirective(
            $this->subject,
            $this->proceed,
            $this->construction('class="" id="footer_links"')
        );

        $this->assertSame('', $result);
    }

    public function testItRefusesAClassNamedThroughATemplateVariable()
    {
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->anything(), ['class' => '$evil']);

        $result = $this->plugin(['Magento\Framework\View\Element\Text'])->aroundBlockDirective(
            $this->subject,
            $this->proceed,
            $this->construction('class=$evil')
        );

        $this->assertSame('', $result);
    }

    public function testItReadsTheLastClassParameterAsCoreDoes()
    {
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->anything(), ['class' => 'Magento\Backend\Block\Widget\Grid\ColumnSet']);

        $result = $this->plugin(['Magento\Framework\View\Element\Text'])->aroundBlockDirective(
            $this->subject,
            $this->proceed,
            $this->construction(
                'class="Magento\Framework\View\Element\Text" '
                . 'class="Magento\Backend\Block\Widget\Grid\ColumnSet"'
            )
        );

        $this->assertSame('', $result);
    }

    public function testItRefusesAClassSpelledWithDoubledSeparators()
    {
        $this->logger->expects($this->once())->method('critical');

        $result = $this->plugin(['Magento\Framework\View\Element\Text'])->aroundBlockDirective(
            $this->subject,
            $this->proceed,
            $this->construction('class="Magento\\\\Backend\\\\Block\\\\Widget\\\\Grid\\\\ColumnSet"')
        );

        $this->assertSame('', $result);
    }

    public function testItAllowsAnAllowlistedClassSpelledWithDoubledSeparators()
    {
        $this->logger->expects($this->never())->method('critical');

        $result = $this->plugin(['Magento\Framework\View\Element\Text'])->aroundBlockDirective(
            $this->subject,
            $this->proceed,
            $this->construction('class="Magento\\\\Framework\\\\View\\\\Element\\\\Text"')
        );

        $this->assertSame('RENDERED', $result);
    }
}
