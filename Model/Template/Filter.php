<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Model\Template;

use Magento\Email\Model\Template\Filter as EmailTemplateFilter;

/**
 * Refuses backend/adminhtml block classes in the {{block}} email template directive.
 *
 * The StyleSmuggler chain reaches an adminhtml grid block through a poisoned email template.
 * Legitimate transactional templates never instantiate a backend block, so the directive drops them.
 *
 * @see https://sansec.io/research/stylesmuggler
 */
class Filter extends EmailTemplateFilter
{
    /**
     * Retrieve Block html directive, minus the restricted classes.
     *
     * @param array $construction
     * @return string
     */
    public function blockDirective($construction)
    {
        $blockParameters = $this->getParameters($construction[2]);

        if (isset($blockParameters['class'])
            && $this->isRestrictedBlockClass((string)$blockParameters['class'])
        ) {
            $this->_logger->warning(
                'Refused to instantiate a restricted block class from a template directive.',
                ['class' => (string)$blockParameters['class']]
            );
            return '';
        }

        return parent::blockDirective($construction);
    }

    /**
     * Whether the given block class is off limits to a template directive.
     *
     * @param string $class
     * @return bool
     */
    private function isRestrictedBlockClass(string $class): bool
    {
        $normalized = ltrim(str_replace('/', '\\', trim($class)), '\\');
        if ($normalized === '') {
            return false;
        }
        if (stripos($normalized, '\\Block\\Adminhtml\\') !== false) {
            return true;
        }
        if (stripos($normalized, 'Magento\\Backend\\Block\\') === 0) {
            return true;
        }
        return false;
    }
}
