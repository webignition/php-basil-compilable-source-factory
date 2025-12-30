<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\EnvironmentValueFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayAccessExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilValueTypeIdentifier\ValueTypeIdentifier;

class ScalarValueHandler
{
    public function __construct(
        private ValueTypeIdentifier $valueTypeIdentifier,
        private EnvironmentValueFactory $environmentValueFactory
    ) {}

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
                        new VariableDependency(VariableNameEnum::PANTHER_CLIENT),
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

        return new ArrayAccessExpression(
            new VariableDependency(VariableNameEnum::ENVIRONMENT_VARIABLE_ARRAY),
            $property
        );
    }

    /**
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
                new VariableDependency(VariableNameEnum::PANTHER_CLIENT),
                $methodName
            );
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_VALUE, $value);
    }
}
