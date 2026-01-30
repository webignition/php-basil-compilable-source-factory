<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\EnvironmentValueFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayAccessExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatingCastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\Statement\ReturnStatement;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
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

            return LiteralExpression::string('$' . $property);
        }

        if ($this->valueTypeIdentifier->isEnvironmentValue($value)) {
            return $this->handleEnvironmentValue($value);
        }

        if ($this->valueTypeIdentifier->isPageProperty($value)) {
            return $this->handlePageProperty($value);
        }

        if ($this->valueTypeIdentifier->isLiteralValue($value)) {
            return LiteralExpression::string($value);
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_VALUE, $value);
    }

    private function handleBrowserProperty(): ExpressionInterface
    {
        $webDriverDimensionVariable = Property::asIntegerVariable('webDriverDimension');

        $bodyContent = new BodyContentCollection()
            ->append(
                new Statement(
                    new AssignmentExpression(
                        $webDriverDimensionVariable,
                        new MethodInvocation(
                            methodName: 'getWebDriver()->manage()->window()->getSize',
                            arguments: new MethodArguments(),
                            mightThrow: true,
                            type: TypeCollection::integer(),
                            parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                        )
                    )
                )
            )
            ->append(
                new EmptyLine()
            )
            ->append(
                new ReturnStatement(
                    new CompositeExpression(
                        [
                            new EncapsulatingCastExpression(
                                new MethodInvocation(
                                    methodName: 'getWidth',
                                    arguments: new MethodArguments(),
                                    mightThrow: true,
                                    type: TypeCollection::integer(),
                                    parent: $webDriverDimensionVariable,
                                ),
                                Type::STRING
                            ),
                            LiteralExpression::void(' . \'x\' . '),
                            new EncapsulatingCastExpression(
                                new MethodInvocation(
                                    methodName: 'getHeight',
                                    arguments: new MethodArguments(),
                                    mightThrow: true,
                                    type: TypeCollection::integer(),
                                    parent: $webDriverDimensionVariable,
                                ),
                                Type::STRING
                            ),
                        ],
                        TypeCollection::string(),
                    ),
                )
            )
        ;

        return new ClosureExpression(new Body($bodyContent));
    }

    private function handleEnvironmentValue(string $value): ExpressionInterface
    {
        $environmentValue = $this->environmentValueFactory->create($value);
        $property = $environmentValue->getProperty();

        return new ArrayAccessExpression(
            Property::asDependency(DependencyName::ENVIRONMENT_VARIABLE_ARRAY),
            $property,
            TypeCollection::string(),
        );
    }

    /**
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
            return new MethodInvocation(
                methodName: $methodName,
                arguments: new MethodArguments(),
                mightThrow: true,
                type: TypeCollection::string(),
                parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
            );
        }

        throw new UnsupportedContentException(UnsupportedContentException::TYPE_VALUE, $value);
    }
}
