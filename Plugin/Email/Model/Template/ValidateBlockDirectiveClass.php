<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Plugin\Email\Model\Template;

use Graycore\StyleSmugglerPatch\Model\Template\BlockDirectiveAllowList;
use Magento\Email\Model\Template\Filter;
use Magento\Framework\Filter\Template\Tokenizer\Parameter;
use Psr\Log\LoggerInterface;

/**
 * Restricts the {{block}} email template directive to an allowlist of block classes.
 *
 * Nothing is allowed by default, so no template can instantiate a block until a merchant opts a
 * class in. The StyleSmuggler chain reaches an adminhtml grid block through a poisoned email
 * template, and core instantiates whatever class the directive names.
 *
 * @see BlockDirectiveAllowList
 * @see https://sansec.io/research/stylesmuggler
 */
class ValidateBlockDirectiveClass
{
    /**
     * @var BlockDirectiveAllowList
     */
    private $allowList;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param BlockDirectiveAllowList $allowList
     * @param LoggerInterface $logger
     */
    public function __construct(
        BlockDirectiveAllowList $allowList,
        LoggerInterface $logger
    ) {
        $this->allowList = $allowList;
        $this->logger = $logger;
    }

    /**
     * Render the directive only when it names an allowed class.
     *
     * A directive with no class parameter names no class for core to resolve, so it is left alone.
     *
     * @param Filter $subject
     * @param callable $proceed
     * @param array $construction
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBlockDirective(Filter $subject, callable $proceed, $construction)
    {
        $class = $this->getClassParameter(is_array($construction) ? ($construction[2] ?? '') : '');

        if ($class === null) {
            return $proceed($construction);
        }

        if (!$this->allowList->isAllowed($class)) {
            $this->logger->critical(
                $this->allowList->isAlwaysRefused($class)
                    ? 'Refused a backend block class in an email template {{block}} directive. '
                        . 'This class cannot be allowlisted.'
                    : 'Refused a block class in an email template {{block}} directive because it '
                        . 'is not on the allowlist.',
                ['class' => $class]
            );

            return '';
        }

        return $proceed($construction);
    }

    /**
     * Read the class parameter out of the raw directive parameters.
     *
     * Tokenized the same way core tokenizes them, minus the variable resolution core applies
     * afterwards: a class named through a template variable is not knowable here, so it arrives
     * as the unresolved token and fails the allowlist.
     *
     * @param string $parameters
     * @return string|null the class the directive names, or null when it names none
     */
    private function getClassParameter($parameters): ?string
    {
        $tokenizer = new Parameter();
        $tokenizer->setString((string)$parameters);
        $tokenized = $tokenizer->tokenize();

        if (!isset($tokenized['class'])) {
            return null;
        }

        return is_string($tokenized['class']) ? $tokenized['class'] : '';
    }
}
