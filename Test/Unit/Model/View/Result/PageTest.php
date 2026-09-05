<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Test\Unit\Model\View\Result;

use Graycore\StyleSmugglerPatch\Model\View\Result\Page;
use Magento\Framework\View\FileSystem as ViewFileSystem;
use Magento\Framework\View\Result\Page as ResultPage;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    /**
     * Assemble the result page by hand: its real constructor pulls in most of the view layer, and
     * renderPage() only reaches for these three properties.
     *
     * @param string $resolvedFileName
     * @return Page
     */
    private function pageResolvingTo($resolvedFileName): Page
    {
        $page = (new \ReflectionClass(Page::class))->newInstanceWithoutConstructor();

        $viewFileSystem = $this->createStub(ViewFileSystem::class);
        $viewFileSystem->method('getTemplateFileName')->willReturn($resolvedFileName);

        $properties = [
            'viewFileSystem' => $viewFileSystem,
            'template' => 'Graycore_StyleSmugglerPatch::page.phtml',
            'viewVars' => [],
        ];
        foreach ($properties as $name => $value) {
            (new \ReflectionProperty(ResultPage::class, $name))->setValue($page, $value);
        }

        return $page;
    }

    /**
     * @param Page $page
     * @return string
     */
    private function renderPage(Page $page)
    {
        return (new \ReflectionMethod(Page::class, 'renderPage'))->invoke($page);
    }

    public function testItRefusesUnsafeTemplatePaths()
    {
        $refused = [
            'php stream wrapper' => 'php://input',
            'php filter wrapper' => 'php://filter/convert.base64-decode/resource=/tmp/x',
            'phar wrapper' => 'phar:///var/www/pub/media/x.jpg/y.phtml',
            'remote wrapper' => 'https://example.com/x.phtml',
            'null byte' => "/var/www/templates/x.phtml\0.jpg",
        ];

        foreach ($refused as $description => $resolvedFileName) {
            try {
                $this->renderPage($this->pageResolvingTo($resolvedFileName));
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

    public function testItRendersAnOrdinaryTemplate()
    {
        $template = tempnam(sys_get_temp_dir(), 'ss') . '.phtml';
        file_put_contents($template, 'rendered by the parent');

        try {
            $this->assertSame(
                'rendered by the parent',
                $this->renderPage($this->pageResolvingTo($template))
            );
        } finally {
            unlink($template);
        }
    }

    public function testItLeavesTheMissingTemplateErrorToCore()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->renderPage($this->pageResolvingTo(false));
    }
}
