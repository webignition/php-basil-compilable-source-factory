<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSource\Factory\ArgumentFactory;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\StatementInterface;

class StatementFactoryCallFactory
{
    private ArgumentFactory $argumentFactory;

    public function __construct(ArgumentFactory $argumentFactory)
    {
        $this->argumentFactory = $argumentFactory;
    }

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
            $this->argumentFactory->create([
                $serializedStatementSource,
            ])
        );
    }
}
