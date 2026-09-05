<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Test\Unit\Plugin\Framework\View\TemplateEngine;

use Graycore\StyleSmugglerPatch\Plugin\Framework\View\TemplateEngine\ValidateTemplatePath;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngine\Php;
use PHPUnit\Framework\TestCase;

class ValidateTemplatePathTest extends TestCase
{
    /**
     * @var ValidateTemplatePath
     */
    private $plugin;

    /**
     * @var Php
     */
    private $subject;

    /**
     * @var BlockInterface
     */
    private $block;

    protected function setUp(): void
    {
        $this->plugin = new ValidateTemplatePath();
        $this->subject = $this->createStub(Php::class);
        $this->block = $this->createStub(BlockInterface::class);
    }

    public function testItAllowsOrdinaryTemplatePaths()
    {
        $allowed = [
            'module template' => '/var/www/vendor/magento/module-catalog/view/frontend/templates/product.phtml',
            'theme template' => '/var/www/app/design/frontend/Vendor/theme/Magento_Catalog/templates/x.phtml',
            'relative path' => 'view/frontend/templates/product.phtml',
            'windows separators' => 'C:\\www\\app\\design\\theme\\templates\\x.phtml',
            'empty string' => '',
            'colon but no wrapper' => '/var/www/templates/odd:name.phtml',
        ];

        foreach ($allowed as $description => $fileName) {
            $this->assertNull(
                $this->plugin->beforeRender($this->subject, $this->block, $fileName),
                $description . ' should be allowed'
            );
        }
    }

    public function testItRefusesUnsafeTemplatePaths()
    {
        $refused = [
            'php stream wrapper' => 'php://input',
            'php filter wrapper' => 'php://filter/convert.base64-decode/resource=/tmp/x',
            'data wrapper' => 'data://text/plain;base64,PD9waHAgcGhwaW5mbygpOw==',
            'remote wrapper' => 'https://example.com/x.phtml',
            'phar wrapper' => 'phar:///var/www/pub/media/x.jpg/y.phtml',
            'wrapper after a directory' => '/var/www/templates/../php://input',
            'null byte' => "/var/www/templates/x.phtml\0.jpg",
            'wrapper written with backslashes' => 'phar:\\\\/var/www/x.phar/y.phtml',
        ];

        foreach ($refused as $description => $fileName) {
            try {
                $this->plugin->beforeRender($this->subject, $this->block, $fileName);
                $this->fail($description . ' should have been refused');
            } catch (\RuntimeException $exception) {
                $this->assertSame(
                    'Refusing to render a template from an unsafe path.',
                    $exception->getMessage(),
                    $description
                );
            }
        }
    }
}
