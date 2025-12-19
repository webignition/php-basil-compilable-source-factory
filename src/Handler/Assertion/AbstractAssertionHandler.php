<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
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
     * @param array<string, string> $operatorToAssertionTemplateMap
     */
    protected function createAssertionStatement(
        AssertionInterface $assertion,
        array $operatorToAssertionTemplateMap,
        Metadata $metadata,
        MethodArgumentsInterface $arguments,
    ): StatementInterface {
        $assertionMethod = $operatorToAssertionTemplateMap[$assertion->getOperator()];

        $serializedMetadata = (string) json_encode($metadata, JSON_PRETTY_PRINT);

        $arguments = $arguments->withArgument(
            $this->argumentFactory->createSingular($serializedMetadata)
        );

        $arguments = $arguments->withFormat(
            MethodArgumentsInterface::FORMAT_STACKED
        );

        $statement = $this->phpUnitCallFactory->createCall($assertionMethod, $arguments);

        return new Statement($statement);
    }
}
