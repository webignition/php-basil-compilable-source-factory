<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\AddCSlashesCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\PhpUnitFailReason;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\StaticObjectProperty;
use webignition\BasilCompilableSourceFactory\Model\Json\FailureMessage;
use webignition\BasilCompilableSourceFactory\Model\Json\IntegerLiteral;
use webignition\BasilCompilableSourceFactory\Model\Json\LiteralInterface;
use webignition\BasilCompilableSourceFactory\Model\Json\StringLiteral;
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

    public function createForActionFailure(StatementInterface $statement): FailureMessage
    {
        return $this->create($statement, PhpUnitFailReason::ACTION_FAILED, []);
    }

    public function createForInvalidLocatorException(
        StatementInterface $statement,
        ExpressionInterface $locatorVariableExpression,
        ExpressionInterface $typeVariableExpression,
    ): FailureMessage {
        $locatorCall = $this->addSlashesCallFactory->create($locatorVariableExpression);
        $resolvedLocatorCall = $this->variableResolver->resolveAndIgnoreUnresolvedVariables($locatorCall);

        $typeCall = $this->addSlashesCallFactory->create($typeVariableExpression);
        $resolvedTypeCall = $this->variableResolver->resolveAndIgnoreUnresolvedVariables($typeCall);

        return $this->create(
            $statement,
            PhpUnitFailReason::INVALID_LOCATOR,
            [
                'locator' => new StringLiteral($resolvedLocatorCall),
                'type' => new StringLiteral($resolvedTypeCall),
            ]
        );
    }

    public function createForAssertionSetupThrowable(StatementInterface $statement): FailureMessage
    {
        return $this->create($statement, PhpUnitFailReason::ASSERTION_SETUP_FAILED, []);
    }

    /**
     * @param array<string, LiteralInterface> $context
     */
    private function create(StatementInterface $statement, PhpUnitFailReason $reason, array $context): FailureMessage
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
            new StringLiteral($resolvedClassCall),
            new IntegerLiteral($resolvedCodeCall),
            new StringLiteral($resolvedMessageCall),
            $context,
        );
    }
}
