<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocationInterface;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

abstract class AbstractAssertionHandler
{
    public function __construct(
        private ArgumentFactory $argumentFactory,
        private PhpUnitCallFactory $phpUnitCallFactory,
    ) {}

    /**
     * @return array<string, string>
     */
    abstract protected function getOperationToAssertionTemplateMap(): array;

    protected function createAssertionStatement(
        AssertionInterface $assertion,
        Metadata $metadata,
        MethodArgumentsInterface $arguments,
    ): StatementInterface {
        return new Statement(
            $this->create(
                $this->getOperationToAssertionTemplateMap()[$assertion->getOperator()],
                $metadata,
                $arguments
            )
        );
    }

    private function create(
        string $assertionMethod,
        Metadata $metadata,
        MethodArgumentsInterface $arguments,
    ): MethodInvocationInterface {
        $serializedMetadata = (string) json_encode($metadata, JSON_PRETTY_PRINT);

        $arguments = $arguments->withArgument(
            $this->argumentFactory->createSingular($serializedMetadata)
        );

        $arguments = $arguments->withFormat(
            MethodArgumentsInterface::FORMAT_STACKED
        );

        return $this->phpUnitCallFactory->createCall($assertionMethod, $arguments);
    }
}
