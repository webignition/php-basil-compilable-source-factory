<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\Expression\ComparisonExpression;
use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilCompilableSourceFactory\ValueTypeIdentifier;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Assertion\AssertionInterface;

class IsRegExpAssertionHandler extends AbstractAssertionHandler
{
    public const ASSERT_FALSE_METHOD = 'assertFalse';

    private AccessorDefaultValueFactory $accessorDefaultValueFactory;
    private DomIdentifierFactory $domIdentifierFactory;
    private IdentifierTypeAnalyser $identifierTypeAnalyser;
    private ValueTypeIdentifier $valueTypeIdentifier;
    private ValueAccessorFactory $valueAccessorFactory;

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'is-regexp' => self::ASSERT_FALSE_METHOD,
    ];

    public function __construct(
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        ValueTypeIdentifier $valueTypeIdentifier,
        ValueAccessorFactory $valueAccessorFactory
    ) {
        parent::__construct($assertionMethodInvocationFactory);

        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->valueTypeIdentifier = $valueTypeIdentifier;
        $this->valueAccessorFactory = $valueAccessorFactory;
    }

    public static function createHandler(): self
    {
        return new IsRegExpAssertionHandler(
            AccessorDefaultValueFactory::createFactory(),
            AssertionMethodInvocationFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            IdentifierTypeAnalyser::create(),
            new ValueTypeIdentifier(),
            ValueAccessorFactory::createFactory()
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
        $identifier = $assertion->getIdentifier();

        if ($this->valueTypeIdentifier->isScalarValue($identifier)) {
            $examinedAccessor = new LiteralExpression($identifier);

            return $this->createIsRegExpAssertionBody($examinedAccessor, $assertion);
        }

        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($identifier)) {
            $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString($identifier);
            if (null === $domIdentifier) {
                throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
            }

            $examinedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull($assertion->getIdentifier());

            return $this->createIsRegExpAssertionBody($examinedAccessor, $assertion);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
    }

    /**
     * @param ExpressionInterface[] $arguments
     * @param string $argumentFormat
     *
     * @return ExpressionInterface
     */
    private function createSetBooleanExpectedValueInvocation(
        array $arguments,
        string $argumentFormat = ObjectMethodInvocation::ARGUMENT_FORMAT_INLINE
    ): ExpressionInterface {
        return $this->createPhpUnitTestCaseObjectMethodInvocation(
            'setBooleanExpectedValue',
            $arguments,
            $argumentFormat
        );
    }

    private function createGetBooleanExpectedValueInvocation(): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation('getBooleanExpectedValue');
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

    private function createIsRegExpAssertionBody(
        ExpressionInterface $examinedAccessor,
        AssertionInterface $assertion
    ): BodyInterface {
        $pregMatchInvocation = new MethodInvocation(
            'preg_match',
            [
                $this->createGetExaminedValueInvocation(),
                new LiteralExpression('null'),
            ]
        );
        $pregMatchInvocation->enableErrorSuppression();

        $identityComparison = new ComparisonExpression(
            $pregMatchInvocation,
            new LiteralExpression('false'),
            '==='
        );

        return new Body([
            new Statement($this->createSetExaminedValueInvocation([
                $examinedAccessor
            ])),
            new Statement($this->createSetBooleanExpectedValueInvocation(
                [
                    $identityComparison
                ],
                MethodInvocation::ARGUMENT_FORMAT_STACKED
            )),
            $this->createAssertionStatement($assertion, [
                $this->createGetBooleanExpectedValueInvocation()
            ]),
        ]);
    }
}
