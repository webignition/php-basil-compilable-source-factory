<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\Expression\ComparisonExpression;
use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class IsRegExpAssertionHandler extends AbstractAssertionHandler
{
    public const ASSERT_FALSE_METHOD = 'assertFalse';

    private DomIdentifierFactory $domIdentifierFactory;
    private IdentifierTypeAnalyser $identifierTypeAnalyser;
    private ValueTypeIdentifier $valueTypeIdentifier;
    private ValueAccessorFactory $valueAccessorFactory;

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'is-regexp' => self::ASSERT_FALSE_METHOD,
    ];

    public function __construct(
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser,
        ValueTypeIdentifier $valueTypeIdentifier,
        ValueAccessorFactory $valueAccessorFactory
    ) {
        parent::__construct($assertionMethodInvocationFactory);

        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->valueTypeIdentifier = $valueTypeIdentifier;
        $this->valueAccessorFactory = $valueAccessorFactory;
    }

    public static function createHandler(): self
    {
        return new IsRegExpAssertionHandler(
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
            $examinedAccessor = $this->valueAccessorFactory->createWithDefaultIfNull($identifier);

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

    private function createIsRegExpAssertionBody(
        ExpressionInterface $examinedAccessor,
        AssertionInterface $assertion
    ): BodyInterface {
        $pregMatchInvocation = new MethodInvocation(
            'preg_match',
            [
                $this->createPhpUnitTestCaseObjectMethodInvocation('getExaminedValue'),
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
            Body::createFromExpressions([
                $this->createPhpUnitTestCaseObjectMethodInvocation('setExaminedValue', [$examinedAccessor]),
                $this->createPhpUnitTestCaseObjectMethodInvocation(
                    'setBooleanExpectedValue',
                    [
                        $identityComparison
                    ],
                    MethodInvocation::ARGUMENT_FORMAT_STACKED
                ),
            ]),
            $this->createAssertionStatement($assertion, [
                $this->createPhpUnitTestCaseObjectMethodInvocation('getBooleanExpectedValue')
            ]),
        ]);
    }
}
