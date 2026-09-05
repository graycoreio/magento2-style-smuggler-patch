<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Disables the handlePayflowProResponse mutation.
 *
 * Replaces \Magento\PaypalGraphQl\Model\Resolver\PayflowProResponse, the injection point for the
 * StyleSmuggler vulnerability (https://sansec.io/research/stylesmuggler). Nothing from the core
 * resolver is constructed or reached; the mutation always fails.
 */
class PayflowProResponse implements ResolverInterface
{
    /**
     * @inheritdoc
     *
     * @throws GraphQlInputException Always.
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        throw new GraphQlInputException(
            __('The handlePayflowProResponse mutation is disabled by Graycore_StyleSmugglerPatch.')
        );
    }
}
