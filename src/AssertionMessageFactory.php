<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\AddCSlashesCallFactory;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\TernaryExpression;
use webignition\BasilCompilableSourceFactory\Model\Json\AssertionMessage;
use webignition\BasilCompilableSourceFactory\Model\Json\LiteralInterface;
use webignition\BasilCompilableSourceFactory\Model\Json\NullLiteral;
use webignition\BasilCompilableSourceFactory\Model\Json\StringLiteral;
use webignition\BasilCompilableSourceFactory\Model\Json\UnquotedLiteral;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\Stubble\VariableResolver;

readonly class AssertionMessageFactory
{
    public function __construct(
        private VariableResolver $variableResolver,
        private AddCSlashesCallFactory $addSlashesCallFactory,
    ) {}

    public static function createFactory(): AssertionMessageFactory
    {
        return new AssertionMessageFactory(
            new VariableResolver(),
            AddCSlashesCallFactory::createFactory(),
        );
    }

    public function create(
        AssertionInterface $assertion,
        ?AssertionArgument $expected,
        ?AssertionArgument $examined,
    ): AssertionMessage {
        $assertionMessageExpected = $this->createAssertionMessageLiteral($expected);
        $assertionMessageExamined = $this->createAssertionMessageLiteral($examined);

        return new AssertionMessage($assertion, $assertionMessageExpected, $assertionMessageExamined);
    }

    private function createAssertionMessageLiteral(?AssertionArgument $argument): LiteralInterface
    {
        if (null === $argument) {
            return new NullLiteral();
        }

        if ('string' === $argument->type) {
            return new StringLiteral(
                $this->variableResolver->resolveAndIgnoreUnresolvedVariables(
                    $this->addSlashesCallFactory->create(
                        new CastExpression($argument->expression, 'string')
                    )
                )
            );
        }

        if ('bool' === $argument->type) {
            return new UnquotedLiteral(
                $this->variableResolver->resolveAndIgnoreUnresolvedVariables(
                    new EncapsulatedExpression(
                        new TernaryExpression(
                            $argument->expression,
                            new LiteralExpression('\'true\''),
                            new LiteralExpression('\'false\''),
                        ),
                    )
                )
            );
        }

        return new NullLiteral();
    }
}
