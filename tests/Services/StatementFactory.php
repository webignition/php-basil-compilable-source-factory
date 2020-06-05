<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Line\ClosureExpression;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\AssignmentStatement;
use webignition\BasilCompilableSource\Line\Statement\ReturnStatement;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\Line\Statement\StatementInterface;
use webignition\BasilCompilableSource\ResolvablePlaceholder;
use webignition\BasilCompilableSource\ResolvingPlaceholder;
use webignition\BasilCompilableSource\VariablePlaceholderInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;

class StatementFactory
{
    public static function createAssertBrowserTitle(string $expectedTitle): StatementInterface
    {
        return new Statement(
            new ObjectMethodInvocation(
                ResolvablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                'assertSame',
                [
                    new LiteralExpression('"' . $expectedTitle . '"'),
                    new ObjectMethodInvocation(
                        ResolvablePlaceholder::createDependency(VariableNames::PANTHER_CLIENT),
                        'getTitle'
                    ),
                ]
            )
        );
    }

    public static function createCrawlerFilterCallForElement(
        string $selector,
        VariablePlaceholderInterface $placeholder
    ): StatementInterface {
        $elementPlaceholder = new ResolvingPlaceholder('element');

        return new AssignmentStatement(
            $placeholder,
            new ClosureExpression(new CodeBlock([
                new AssignmentStatement(
                    $elementPlaceholder,
                    new ObjectMethodInvocation(
                        ResolvablePlaceholder::createDependency(VariableNames::PANTHER_CRAWLER),
                        'filter',
                        [
                            new LiteralExpression('\'' . $selector . '\''),
                        ]
                    )
                ),
                new ReturnStatement(
                    new ObjectMethodInvocation(
                        $elementPlaceholder,
                        'getElement',
                        [
                            new LiteralExpression('0'),
                        ]
                    )
                ),
            ]))
        );
    }

    public static function createAssertSame(string $expected, string $actual): StatementInterface
    {
        return self::createAssertExpectedActual('assertSame', $expected, $actual);
    }

    public static function createCrawlerActionCallForElement(string $selector, string $action): StatementInterface
    {
        $elementPlaceholder = new ResolvingPlaceholder('element');

        return new Statement(
            new ClosureExpression(new CodeBlock([
                new AssignmentStatement(
                    $elementPlaceholder,
                    new ObjectMethodInvocation(
                        ResolvablePlaceholder::createDependency(VariableNames::PANTHER_CRAWLER),
                        'filter',
                        [
                            new LiteralExpression('\'' . $selector . '\''),
                        ]
                    )
                ),
                new AssignmentStatement(
                    $elementPlaceholder,
                    new ObjectMethodInvocation(
                        $elementPlaceholder,
                        'getElement',
                        [
                            new LiteralExpression('0'),
                        ]
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
                ResolvablePlaceholder::createDependency(VariableNames::PANTHER_CLIENT),
                $action
            )
        );
    }

    public static function createCrawlerFilterCall(
        string $selector,
        VariablePlaceholderInterface $placeholder
    ): StatementInterface {
        return new AssignmentStatement(
            $placeholder,
            new ObjectMethodInvocation(
                ResolvablePlaceholder::createDependency(VariableNames::PANTHER_CRAWLER),
                'filter',
                [
                    new LiteralExpression('\'' . $selector . '\'')
                ]
            )
        );
    }

    public static function createAssertFalse(string $actual): StatementInterface
    {
        return new Statement(
            new ObjectMethodInvocation(
                ResolvablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                'assertFalse',
                [
                    new LiteralExpression($actual)
                ]
            )
        );
    }

    public static function createAssertTrue(string $actual): StatementInterface
    {
        return new Statement(
            new ObjectMethodInvocation(
                ResolvablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                'assertTrue',
                [
                    new LiteralExpression($actual)
                ]
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
                ResolvablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                $methodName,
                [
                    new LiteralExpression($expected),
                    new LiteralExpression($actual)
                ]
            )
        );
    }
}
