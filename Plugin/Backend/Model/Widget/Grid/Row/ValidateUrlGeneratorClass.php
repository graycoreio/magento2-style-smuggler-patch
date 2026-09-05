<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Plugin\Backend\Model\Widget\Grid\Row;

use Magento\Backend\Model\Widget\Grid\Row\GeneratorInterface;
use Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory;

/**
 * Validates the requested generator class before the factory instantiates it.
 *
 * Core checks the type only after \Magento\Framework\ObjectManagerInterface::create() has already
 * built the object and resolved its arguments, so a caller can reach an arbitrary class through
 * the factory. Checking first means those arguments are never resolved.
 *
 * @see https://sansec.io/research/stylesmuggler
 */
class ValidateUrlGeneratorClass
{
    /**
     * Reject anything that is not a row URL generator.
     *
     * @param UrlGeneratorFactory $subject
     * @param string $generatorClassName
     * @param array $arguments
     * @return array
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCreateUrlGenerator(
        UrlGeneratorFactory $subject,
        $generatorClassName,
        array $arguments = []
    ) {
        if (!is_string($generatorClassName)
            || !is_subclass_of($generatorClassName, GeneratorInterface::class, true)
        ) {
            throw new \InvalidArgumentException('Passed wrong parameters');
        }

        return [$generatorClassName, $arguments];
    }
}
