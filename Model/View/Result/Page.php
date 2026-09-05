<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Model\View\Result;

use Graycore\StyleSmugglerPatch\Model\Template\ChecksTemplatePath;
use Magento\Framework\View\Result\Page as ResultPage;

/**
 * Refuses to render a page template from a path that is not a plain local file.
 *
 * @see https://sansec.io/research/stylesmuggler
 */
class Page extends ResultPage
{
    use ChecksTemplatePath;

    /**
     * Render page template.
     *
     * @return string
     * @throws \Exception
     */
    protected function renderPage()
    {
        $fileName = $this->viewFileSystem->getTemplateFileName($this->template);
        if ($fileName && $this->isUnsafeTemplatePath((string)$fileName)) {
            throw new \RuntimeException('Refusing to render a template from an unsafe path.');
        }

        return parent::renderPage();
    }
}
