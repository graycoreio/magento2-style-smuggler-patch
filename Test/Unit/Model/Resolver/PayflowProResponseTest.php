<?php

declare(strict_types=1);

namespace Graycore\StyleSmugglerPatch\Test\Unit\Model\Resolver;

use Graycore\StyleSmugglerPatch\Model\Resolver\PayflowProResponse;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\TestCase;

class PayflowProResponseTest extends TestCase
{
    public function testItThrowsOnAWellFormedRequest()
    {
        $resolver = new PayflowProResponse();

        $this->expectException(GraphQlInputException::class);

        $resolver->resolve(
            $this->createStub(Field::class),
            null,
            $this->createStub(ResolveInfo::class),
            null,
            ['input' => ['cart_id' => 'abc123', 'paypal_payload' => 'RESULT=0']]
        );
    }

    public function testItThrowsOnAnEmptyRequest()
    {
        $resolver = new PayflowProResponse();

        $this->expectException(GraphQlInputException::class);

        $resolver->resolve(
            $this->createStub(Field::class),
            null,
            $this->createStub(ResolveInfo::class)
        );
    }
}
