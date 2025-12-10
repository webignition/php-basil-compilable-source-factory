<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocationInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;

class AssertionMethodInvocationFactory
{
    public static function createFactory(): AssertionMethodInvocationFactory
    {
        return new AssertionMethodInvocationFactory();
    }

    public function create(
        string $assertionMethod,
        Metadata $metadata,
        MethodArgumentsInterface $arguments,
    ): MethodInvocationInterface {
        $serializedMetadata = (string) json_encode($metadata, JSON_PRETTY_PRINT);
        $quotedSerializedMetadata = addslashes($serializedMetadata);

        $arguments = $arguments->withArgument(new LiteralExpression("'" . $quotedSerializedMetadata . "'"));

        return new ObjectMethodInvocation(
            new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
            $assertionMethod,
            $arguments->withFormat(MethodArgumentsInterface::FORMAT_STACKED)
        );
    }
}
