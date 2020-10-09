<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\Expression\ComparisonExpression;
use webignition\BasilCompilableSource\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\MethodArguments\MethodArguments;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilModels\Assertion\AssertionInterface;

class ScalarExistenceAssertionHandler extends AbstractAssertionHandler
{
    public const ASSERT_TRUE_METHOD = 'assertTrue';
    public const ASSERT_FALSE_METHOD = 'assertFalse';

    private ScalarValueHandler $scalarValueHandler;

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'exists' => self::ASSERT_TRUE_METHOD,
        'not-exists' => self::ASSERT_FALSE_METHOD,
    ];

    public function __construct(
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        ScalarValueHandler $scalarValueHandler
    ) {
        parent::__construct($assertionMethodInvocationFactory);

        $this->scalarValueHandler = $scalarValueHandler;
    }

    public static function createHandler(): self
    {
        return new ScalarExistenceAssertionHandler(
            AssertionMethodInvocationFactory::createFactory(),
            ScalarValueHandler::createHandler()
        );
    }

    protected function getOperationToAssertionTemplateMap(): array
    {
        return self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP;
    }

    /**
     * @param AssertionInterface $assertion
     *
     * @return BodyInterface
     *
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion): BodyInterface
    {
        $nullComparisonExpression = new ComparisonExpression(
            $this->scalarValueHandler->handle($assertion->getIdentifier()),
            new LiteralExpression('null'),
            '??'
        );

        $setBooleanExaminedValueInvocation = $this->createPhpUnitTestCaseObjectMethodInvocation(
            'setBooleanExaminedValue',
            new MethodArguments([
                new ComparisonExpression(
                    new EncapsulatedExpression($nullComparisonExpression),
                    new LiteralExpression('null'),
                    '!=='
                ),
            ])
        );

        $assertionStatement = $this->createAssertionStatement(
            $assertion,
            new MethodArguments([
                $this->createPhpUnitTestCaseObjectMethodInvocation('getBooleanExaminedValue')
            ])
        );

        return new Body([
            new Statement($setBooleanExaminedValueInvocation),
            $assertionStatement,
        ]);
    }
}
