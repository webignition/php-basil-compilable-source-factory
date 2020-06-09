<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\Expression\CastExpression;
use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\Statement\StatementInterface;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Assertion\AssertionInterface;

class ComparisonAssertionHandler
{
    public const ASSERT_EQUALS_METHOD = 'assertEquals';
    public const ASSERT_NOT_EQUALS_METHOD = 'assertNotEquals';
    public const ASSERT_STRING_CONTAINS_STRING_METHOD = 'assertStringContainsString';
    public const ASSERT_STRING_NOT_CONTAINS_STRING_METHOD = 'assertStringNotContainsString';
    public const ASSERT_MATCHES_METHOD = 'assertRegExp';

    private AccessorDefaultValueFactory $accessorDefaultValueFactory;
    private AssertionMethodInvocationFactory $assertionMethodInvocationFactory;
    private ValueAccessorFactory $valueAccessorFactory;

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'includes' => self::ASSERT_STRING_CONTAINS_STRING_METHOD,
        'excludes' => self::ASSERT_STRING_NOT_CONTAINS_STRING_METHOD,
        'is' => self::ASSERT_EQUALS_METHOD,
        'is-not' => self::ASSERT_NOT_EQUALS_METHOD,
        'matches' => self::ASSERT_MATCHES_METHOD,
    ];

    /**
     * @var string[]
     */
    private array $methodsWithStringArguments = [
        self::ASSERT_STRING_CONTAINS_STRING_METHOD,
        self::ASSERT_STRING_NOT_CONTAINS_STRING_METHOD,
    ];

    public function __construct(
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        ValueAccessorFactory $valueAccessorFactory
    ) {
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->assertionMethodInvocationFactory = $assertionMethodInvocationFactory;
        $this->valueAccessorFactory = $valueAccessorFactory;
    }

    public static function createHandler(): self
    {
        return new ComparisonAssertionHandler(
            AccessorDefaultValueFactory::createFactory(),
            AssertionMethodInvocationFactory::createFactory(),
            ValueAccessorFactory::createFactory()
        );
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
        $assertionMethod = self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP[$assertion->getOperator()];

        $examinedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull($assertion->getIdentifier());
        $expectedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull($assertion->getValue());

        $assertionArguments = [
            $this->createGetExpectedValueInvocation(),
            $this->createGetExaminedValueInvocation(),
        ];

        $isStringArgumentAssertionMethod = in_array($assertionMethod, $this->methodsWithStringArguments);
        if ($isStringArgumentAssertionMethod) {
            array_walk($assertionArguments, function (ExpressionInterface &$expression) {
                $expression = new CastExpression($expression, 'string');
            });
        }

        return new Body([
            new Statement($this->createSetExpectedValueInvocation([$expectedAccessor])),
            new Statement($this->createSetExaminedValueInvocation([$examinedAccessor])),
            $this->createAssertionStatement($assertion, $assertionArguments),
        ]);
    }

    /**
     * @param ExpressionInterface[] $arguments
     *
     * @return ExpressionInterface
     */
    private function createSetExaminedValueInvocation(array $arguments): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation(
            'setExaminedValue',
            $arguments
        );
    }

    private function createGetExaminedValueInvocation(): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation('getExaminedValue');
    }

    private function createGetExpectedValueInvocation(): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation('getExpectedValue');
    }

    /**
     * @param ExpressionInterface[] $arguments
     *
     * @return ExpressionInterface
     */
    private function createSetExpectedValueInvocation(array $arguments): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation(
            'setExpectedValue',
            $arguments
        );
    }

    /**
     * @param string $methodName
     * @param ExpressionInterface[] $arguments
     * @param string $argumentFormat
     *
     * @return ExpressionInterface
     */
    private function createPhpUnitTestCaseObjectMethodInvocation(
        string $methodName,
        array $arguments = [],
        string $argumentFormat = ObjectMethodInvocation::ARGUMENT_FORMAT_INLINE
    ): ExpressionInterface {
        return new ObjectMethodInvocation(
            new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
            $methodName,
            $arguments,
            $argumentFormat
        );
    }

    /**
     * @param AssertionInterface $assertion
     * @param ExpressionInterface[] $arguments
     *
     * @return StatementInterface
     */
    private function createAssertionStatement(AssertionInterface $assertion, array $arguments): StatementInterface
    {
        return new Statement(
            $this->assertionMethodInvocationFactory->create(
                self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP[$assertion->getOperator()],
                $arguments
            )
        );
    }
}
