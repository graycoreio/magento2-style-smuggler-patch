<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Test\Unit\Plugin\Backend\Model\Widget\Grid\Row;

use Graycore\StyleSmugglerPatch\Plugin\Backend\Model\Widget\Grid\Row\ValidateUrlGeneratorClass;
use Magento\Backend\Model\Widget\Grid\Row\UrlGenerator;
use Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ValidateUrlGeneratorClassTest extends TestCase
{
    /**
     * @var ValidateUrlGeneratorClass
     */
    private $plugin;

    /**
     * @var UrlGeneratorFactory
     */
    private $subject;

    protected function setUp(): void
    {
        $this->plugin = new ValidateUrlGeneratorClass();
        $this->subject = $this->createStub(UrlGeneratorFactory::class);
    }

    public function testItPassesThroughARealRowUrlGenerator()
    {
        $arguments = ['path' => 'sales/order/view'];

        $this->assertSame(
            [UrlGenerator::class, $arguments],
            $this->plugin->beforeCreateUrlGenerator($this->subject, UrlGenerator::class, $arguments)
        );
    }

    #[DataProvider('rejectedClassProvider')]
    public function testItRejectsAnythingThatIsNotARowUrlGenerator($generatorClassName)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Passed wrong parameters');

        $this->plugin->beforeCreateUrlGenerator($this->subject, $generatorClassName, ['data' => 'x']);
    }

    /**
     * @return array
     */
    public static function rejectedClassProvider()
    {
        return [
            'arbitrary core class' => [\Magento\Framework\DataObject::class],
            'the interface itself' => [\Magento\Backend\Model\Widget\Grid\Row\GeneratorInterface::class],
            'class that does not exist' => ['Not\A\Real\Class'],
            'empty string' => [''],
            'not a string' => [['class' => \Magento\Framework\DataObject::class]],
            'null' => [null],
        ];
    }
}
