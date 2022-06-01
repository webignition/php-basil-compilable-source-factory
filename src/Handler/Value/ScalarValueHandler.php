<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\EmptyLine;
use webignition\BasilCompilableSource\Expression\AssignmentExpression;
use webignition\BasilCompilableSource\Expression\CastExpression;
use webignition\BasilCompilableSource\Expression\ClosureExpression;
use webignition\BasilCompilableSource\Expression\CompositeExpression;
use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\Expression\ReturnExpression;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSource\VariableName;
use webignition\BasilCompilableSourceFactory\EnvironmentValueFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class ScalarValueHandler
{
    public function __construct(
        private ValueTypeIdentifier $valueTypeIdentifier,
        private EnvironmentValueFactory $environmentValueFactory
    ) {
    }

    public static function createHandler(): ScalarValueHandler
    {
        return new ScalarValueHandler(
            new ValueTypeIdentifier(),
            EnvironmentValueFactory::createFactory()
        );
    }

    /**
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

        if ($this->valueTypeIdentifier->isEnvironmentValue($value)) {
            return $this->handleEnvironmentValue($value);
        }

        if ($this->valueTypeIdentifier->isPageProperty($value)) {
            return $this->handlePageProperty($value);
        }

        if ($this->valueTypeIdentifier->isLiteralValue($value)) {
            return new LiteralExpression($value);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_VALUE, $value);
    }

    private function handleBrowserProperty(): ExpressionInterface
    {
        $webDriverDimensionPlaceholder = new VariableName('webDriverDimension');

        return new ClosureExpression(new Body([
            new Statement(
                new AssignmentExpression(
                    $webDriverDimensionPlaceholder,
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableNames::PANTHER_CLIENT),
                        'getWebDriver()->manage()->window()->getSize'
                    )
                )
            ),
            new EmptyLine(),
            new Statement(
                new ReturnExpression(
                    new CompositeExpression([
                        new CastExpression(
                            new ObjectMethodInvocation($webDriverDimensionPlaceholder, 'getWidth'),
                            'string'
                        ),
                        new LiteralExpression(' . \'x\' . '),
                        new CastExpression(
                            new ObjectMethodInvocation($webDriverDimensionPlaceholder, 'getHeight'),
                            'string'
                        ),
                    ])
                )
            ),
        ]));
    }

    private function handleEnvironmentValue(string $value): ExpressionInterface
    {
        $environmentValue = $this->environmentValueFactory->create($value);
        $property = $environmentValue->getProperty();

        return new CompositeExpression([
            new VariableDependency('ENV'),
            new LiteralExpression(sprintf('[\'%s\']', $property)),
        ]);
    }

    /**
     * @throws UnsupportedContentException
     *
     * @return ObjectMethodInvocation
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
                new VariableDependency(VariableNames::PANTHER_CLIENT),
                $methodName
            );
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_VALUE, $value);
    }
}
