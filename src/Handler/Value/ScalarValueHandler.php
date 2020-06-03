<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Line\CastExpression;
use webignition\BasilCompilableSource\Line\ClosureExpression;
use webignition\BasilCompilableSource\Line\CompositeExpression;
use webignition\BasilCompilableSource\Line\EmptyLine;
use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\ReturnStatement;
use webignition\BasilCompilableSource\ResolvablePlaceholder;
use webignition\BasilCompilableSource\ResolvingPlaceholder;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\EnvironmentValue;
use webignition\BasilCompilableSourceFactory\ModelFactory\EnvironmentValueFactory;
use webignition\BasilCompilableSourceFactory\ValueTypeIdentifier;
use webignition\BasilCompilableSourceFactory\VariableNames;

class ScalarValueHandler
{
    private ValueTypeIdentifier $valueTypeIdentifier;
    private EnvironmentValueFactory $environmentValueFactory;

    public function __construct(
        ValueTypeIdentifier $valueTypeIdentifier,
        EnvironmentValueFactory $environmentValueFactory
    ) {
        $this->valueTypeIdentifier = $valueTypeIdentifier;
        $this->environmentValueFactory = $environmentValueFactory;
    }

    public static function createHandler(): ScalarValueHandler
    {
        return new ScalarValueHandler(
            new ValueTypeIdentifier(),
            EnvironmentValueFactory::createFactory()
        );
    }

    /**
     * @param string $value
     *
     * @return ExpressionInterface
     *
     * @throws UnsupportedContentException
     */
    public function handle(string $value): ExpressionInterface
    {
        if ($this->valueTypeIdentifier->isBrowserProperty($value)) {
            return $this->handleBrowserProperty();
        }

        if ($this->valueTypeIdentifier->isDataParameter($value)) {
            $property = (string) preg_replace('/^\$data\./', '', $value);

            return new LiteralExpression('$' . $property);
        }

        if (EnvironmentValue::is($value)) {
            return $this->handleEnvironmentValue($value);
        }

        if ($this->valueTypeIdentifier->isPageProperty($value)) {
            return $this->handlePageProperty($value);
        }

        if ($this->valueTypeIdentifier->isLiteralValue($value)) {
            return new LiteralExpression((string) $value);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_VALUE, $value);
    }

    private function handleBrowserProperty(): ExpressionInterface
    {
        $webDriverDimensionPlaceholder = new ResolvingPlaceholder('webDriverDimension');

        return new ClosureExpression(new CodeBlock([
            new AssignmentStatement(
                $webDriverDimensionPlaceholder,
                new ObjectMethodInvocation(
                    ResolvablePlaceholder::createDependency(VariableNames::PANTHER_CLIENT),
                    'getWebDriver()->manage()->window()->getSize'
                )
            ),
            new EmptyLine(),
            new ReturnStatement(
                new CompositeExpression([
                    new CastExpression(
                        new ObjectMethodInvocation(
                            $webDriverDimensionPlaceholder,
                            'getWidth',
                            [],
                            MethodInvocation::ARGUMENT_FORMAT_INLINE
                        ),
                        'string'
                    ),
                    new LiteralExpression(' . \'x\' . '),
                    new CastExpression(
                        new ObjectMethodInvocation(
                            $webDriverDimensionPlaceholder,
                            'getHeight',
                            [],
                            MethodInvocation::ARGUMENT_FORMAT_INLINE
                        ),
                        'string'
                    ),
                ])
            )
        ]));
    }

    private function handleEnvironmentValue(string $value): ExpressionInterface
    {
        $environmentValue = $this->environmentValueFactory->create($value);
        $property = $environmentValue->getProperty();

        return new CompositeExpression([
            ResolvablePlaceholder::createDependency('ENV'),
            new LiteralExpression(sprintf('[\'%s\']', $property)),
        ]);
    }

    /**
     * @param string $value
     *
     * @return ObjectMethodInvocation
     *
     * @throws UnsupportedContentException
     */
    private function handlePageProperty(string $value): ExpressionInterface
    {
        $property = (string) preg_replace('/^\$page\./', '', $value);

        $methodNameMap = [
            'title' => 'getTitle',
            'url' => 'getCurrentURL',
        ];

        $methodName = $methodNameMap[$property] ?? null;

        if (is_string($methodName)) {
            return new ObjectMethodInvocation(
                ResolvablePlaceholder::createDependency(VariableNames::PANTHER_CLIENT),
                $methodName
            );
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_VALUE, $value);
    }
}
