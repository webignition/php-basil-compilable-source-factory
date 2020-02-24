<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\AssertionFailureMessageFactory;
use webignition\BasilModels\Assertion\AssertionInterface;

class AssertionFailureMessageFactoryFactory
{
    /**
     * @param array[] $createForAssertionCalls
     *
     * @return AssertionFailureMessageFactory
     */
    public static function create(
        TestCase $context,
        array $createForAssertionCalls
    ): AssertionFailureMessageFactory {
        $assertionFailureMessageFactory = \Mockery::mock(AssertionFailureMessageFactory::class);

        $assertionFailureMessageFactory
            ->shouldReceive('createForAssertion')
            ->times(count($createForAssertionCalls))
            ->andReturnUsing(function (AssertionInterface $assertion) use ($context, $createForAssertionCalls) {
                $data = $createForAssertionCalls[$assertion->getSource()];

                $context->assertEquals($data['assertion'], $assertion);

                return $data['message'];
            });

        return $assertionFailureMessageFactory;
    }
}
