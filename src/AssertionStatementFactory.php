<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

class AssertionStatementFactory
{
    public function __construct(
        private ArgumentFactory $argumentFactory,
        private PhpUnitCallFactory $phpUnitCallFactory,
    ) {}

    public static function createFactory(): self
    {
        return new AssertionStatementFactory(
            ArgumentFactory::createFactory(),
            PhpUnitCallFactory::createFactory(),
        );
    }

    /**
     * @param non-empty-string $assertionMethod
     */
    public function create(
        string $assertionMethod,
        Metadata $metadata,
        MethodArgumentsInterface $arguments,
    ): StatementInterface {
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
