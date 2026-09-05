<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Plugin\Framework\View\TemplateEngine;

use Graycore\StyleSmugglerPatch\Model\Template\ChecksTemplatePath;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngine\Php;

/**
 * Refuses to render a block template from a path that is not a plain local file.
 *
 * @see https://sansec.io/research/stylesmuggler
 */
class ValidateTemplatePath
{
    use ChecksTemplatePath;

    /**
     * Check the path before the engine includes it.
     *
     * @param Php $subject
     * @param BlockInterface $block
     * @param string $fileName
     * @param array $dictionary
     * @return void
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeRender(
        Php $subject,
        BlockInterface $block,
        $fileName,
        array $dictionary = []
    ) {
        if ($this->isUnsafeTemplatePath((string)$fileName)) {
            throw new \RuntimeException('Refusing to render a template from an unsafe path.');
        }
    }
}
