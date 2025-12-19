<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocationInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;

class AssertionMethodInvocationFactory
{
    public function __construct(
        private readonly ArgumentFactory $argumentFactory,
    ) {}

    public static function createFactory(): AssertionMethodInvocationFactory
    {
        return new AssertionMethodInvocationFactory(
            ArgumentFactory::createFactory(),
        );
    }

    public function create(
        string $assertionMethod,
        Metadata $metadata,
        MethodArgumentsInterface $arguments,
    ): MethodInvocationInterface {
        $serializedMetadata = (string) json_encode($metadata, JSON_PRETTY_PRINT);

        $arguments = $arguments->withArgument(
            $this->argumentFactory->createSingular($serializedMetadata)
        );

        return new ObjectMethodInvocation(
            new VariableDependency(VariableName::PHPUNIT_TEST_CASE),
            $assertionMethod,
            $arguments->withFormat(MethodArgumentsInterface::FORMAT_STACKED)
        );
    }
}
