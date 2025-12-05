<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\StatementInterface;

class StatementFactoryCallFactory
{
    public function __construct(
        private ArgumentFactory $argumentFactory
    ) {}

    public static function createFactory(): self
    {
        return new StatementFactoryCallFactory(
            ArgumentFactory::createFactory()
        );
    }

    public function create(StatementInterface $statement): ObjectMethodInvocation
    {
        $objectPlaceholderName = $statement instanceof AssertionInterface
            ? VariableNames::ASSERTION_FACTORY
            : VariableNames::ACTION_FACTORY;

        $serializedStatementSource = (string) json_encode($statement, JSON_PRETTY_PRINT);

        return new ObjectMethodInvocation(
            new VariableDependency($objectPlaceholderName),
            'createFromJson',
            new MethodArguments(
                $this->argumentFactory->create($serializedStatementSource)
            )
        );
    }
}
