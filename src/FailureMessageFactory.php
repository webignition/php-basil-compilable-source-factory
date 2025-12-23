<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\AddCSlashesCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\PhpUnitFailReason;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\StaticObjectProperty;
use webignition\BasilCompilableSourceFactory\Model\Json\FailureMessage;
use webignition\BasilCompilableSourceFactory\Model\Json\Literal;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilModels\Model\StatementInterface;
use webignition\Stubble\VariableResolver;

readonly class FailureMessageFactory
{
    public function __construct(
        private VariableResolver $variableResolver,
        private AddCSlashesCallFactory $addSlashesCallFactory,
    ) {}

    public static function createFactory(): FailureMessageFactory
    {
        return new FailureMessageFactory(
            new VariableResolver(),
            AddCSlashesCallFactory::createFactory(),
        );
    }

    public function create(StatementInterface $statement, PhpUnitFailReason $reason): FailureMessage
    {
        $exceptionVariableName = CatchExpression::getVariableName();

        $classCall = $this->addSlashesCallFactory->create(
            new StaticObjectProperty($exceptionVariableName, 'class')
        );
        $resolvedClassCall = $this->variableResolver->resolveAndIgnoreUnresolvedVariables($classCall);

        $codeCall = new ObjectMethodInvocation($exceptionVariableName, 'getCode');
        $resolvedCodeCall = $this->variableResolver->resolveAndIgnoreUnresolvedVariables($codeCall);

        $messageCall = $this->addSlashesCallFactory->create(
            new ObjectMethodInvocation($exceptionVariableName, 'getMessage')
        );
        $resolvedMessageCall = $this->variableResolver->resolveAndIgnoreUnresolvedVariables($messageCall);

        return new FailureMessage(
            $statement,
            $reason->value,
            new Literal($resolvedClassCall),
            new Literal($resolvedCodeCall),
            new Literal($resolvedMessageCall),
        );
    }
}
