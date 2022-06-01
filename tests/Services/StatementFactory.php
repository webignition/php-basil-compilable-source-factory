<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Expression\AssignmentExpression;
use webignition\BasilCompilableSource\Expression\ClosureExpression;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\Expression\ReturnExpression;
use webignition\BasilCompilableSource\MethodArguments\MethodArguments;
use webignition\BasilCompilableSource\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Statement\Statement;
use webignition\BasilCompilableSource\Statement\StatementInterface;
use webignition\BasilCompilableSource\VariableDependency;
use webignition\BasilCompilableSource\VariableName;
use webignition\BasilCompilableSource\VariablePlaceholderInterface;
use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;

class StatementFactory
{
    public static function createAssertBrowserTitle(string $expectedTitle): StatementInterface
    {
        $argumentFactory = ArgumentFactory::createFactory();

        return new Statement(
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                'assertSame',
                new MethodArguments(
                    $argumentFactory->create(
                        $expectedTitle,
                        new ObjectMethodInvocation(
                            new VariableDependency(VariableNames::PANTHER_CLIENT),
                            'getTitle'
                        ),
                    )
                )
            )
        );
    }

    public static function createCrawlerFilterCallForElement(
        string $selector,
        VariablePlaceholderInterface $placeholder
    ): StatementInterface {
        $elementPlaceholder = new VariableName('element');
        $argumentFactory = ArgumentFactory::createFactory();

        return new Statement(
            new AssignmentExpression(
                $placeholder,
                new ClosureExpression(new Body([
                    new Statement(
                        new AssignmentExpression(
                            $elementPlaceholder,
                            new ObjectMethodInvocation(
                                new VariableDependency(VariableNames::PANTHER_CRAWLER),
                                'filter',
                                new MethodArguments($argumentFactory->create($selector))
                            )
                        )
                    ),
                    new Statement(
                        new ReturnExpression(
                            new ObjectMethodInvocation(
                                $elementPlaceholder,
                                'getElement',
                                new MethodArguments($argumentFactory->create(0))
                            )
                        )
                    ),
                ]))
            )
        );
    }

    public static function createAssertSame(string $expected, string $actual): StatementInterface
    {
        return self::createAssertExpectedActual('assertSame', $expected, $actual);
    }

    public static function createAssertEquals(string $expected, string $actual): StatementInterface
    {
        return self::createAssertExpectedActual('assertEquals', $expected, $actual);
    }

    public static function createCrawlerActionCallForElement(string $selector, string $action): StatementInterface
    {
        $elementPlaceholder = new VariableName('element');
        $argumentFactory = ArgumentFactory::createFactory();

        return new Statement(
            new ClosureExpression(new Body([
                new Statement(
                    new AssignmentExpression(
                        $elementPlaceholder,
                        new ObjectMethodInvocation(
                            new VariableDependency(VariableNames::PANTHER_CRAWLER),
                            'filter',
                            new MethodArguments($argumentFactory->create($selector))
                        )
                    )
                ),
                new Statement(
                    new AssignmentExpression(
                        $elementPlaceholder,
                        new ObjectMethodInvocation(
                            $elementPlaceholder,
                            'getElement',
                            new MethodArguments($argumentFactory->create(0))
                        )
                    )
                ),
                new Statement(
                    new ObjectMethodInvocation(
                        $elementPlaceholder,
                        $action
                    )
                ),
            ]))
        );
    }

    public static function createClientAction(string $action): StatementInterface
    {
        return new Statement(
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PANTHER_CLIENT),
                $action
            )
        );
    }

    public static function createCrawlerFilterCall(
        string $selector,
        VariablePlaceholderInterface $placeholder
    ): StatementInterface {
        $argumentFactory = ArgumentFactory::createFactory();

        return new Statement(
            new AssignmentExpression(
                $placeholder,
                new ObjectMethodInvocation(
                    new VariableDependency(VariableNames::PANTHER_CRAWLER),
                    'filter',
                    new MethodArguments($argumentFactory->create($selector))
                )
            )
        );
    }

    public static function createAssertFalse(string $actual): StatementInterface
    {
        return new Statement(
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                'assertFalse',
                new MethodArguments([
                    new LiteralExpression($actual)
                ])
            )
        );
    }

    public static function createAssertTrue(string $actual): StatementInterface
    {
        return new Statement(
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                'assertTrue',
                new MethodArguments([
                    new LiteralExpression($actual)
                ])
            )
        );
    }

    public static function createAssertCount(string $expected, string $actual): StatementInterface
    {
        return self::createAssertExpectedActual('assertCount', $expected, $actual);
    }

    public static function createAssertInstanceOf(string $expected, string $actual): StatementInterface
    {
        return self::createAssertExpectedActual('assertInstanceOf', $expected, $actual);
    }

    private static function createAssertExpectedActual(
        string $methodName,
        string $expected,
        string $actual
    ): StatementInterface {
        return new Statement(
            new ObjectMethodInvocation(
                new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                $methodName,
                new MethodArguments([
                    new LiteralExpression($expected),
                    new LiteralExpression($actual)
                ])
            )
        );
    }
}
