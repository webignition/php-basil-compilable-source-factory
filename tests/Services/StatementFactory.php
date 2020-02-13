<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\Line\Statement\StatementInterface;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\VariableNames;

class StatementFactory
{
//    /**
//     * @param string $contentTemplate
//     * @param array<VariablePlaceholder> $variableDependencies
//     *
//     * @return StatementInterface
//     */
//    public static function create(string $contentTemplate, array $variableDependencies): StatementInterface
//    {
//        $content = sprintf($contentTemplate, ...$variableDependencies);
//
//        $metadata = (new Metadata())
//            ->withVariableDependencies(new VariablePlaceholderCollection($variableDependencies));
//
//        return new Statement($content, $metadata);
//    }

    public static function createAssertBrowserTitle(string $expectedTitle): StatementInterface
    {
        return new Statement(
            new ObjectMethodInvocation(
                VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                'assertSame',
                [
                    new LiteralExpression('"' . $expectedTitle . '"'),
                    new ObjectMethodInvocation(
                        VariablePlaceholder::createDependency(VariableNames::PANTHER_CLIENT),
                        'getTitle'
                    ),
                ]
            )
        );
    }

//    public static function createCrawlerFilterCallForElement(
//        string $selector,
//        string $variableName = 'variableName'
//    ): StatementInterface {
//        return self::create(
//            $variableName . ' = %s->filter(\'' . $selector . '\')->getElement(0)',
//            [
//                new VariablePlaceholder(VariableNames::PANTHER_CRAWLER),
//            ]
//        );
//    }

    public static function createAssertSame(string $expected, string $actual): StatementInterface
    {
        return self::createAssertExpectedActual('assertSame', $expected, $actual);
    }

//    public static function createCrawlerActionCallForElement(string $selector, string $action): StatementInterface
//    {
//        return self::create(
//            '%s->filter(\'' . $selector . '\')->getElement(0)->' . $action . '()',
//            [
//                PlaceholderFactory::pantherCrawler(),
//            ]
//        );
//    }

//    public static function createClientAction(string $action): StatementInterface
//    {
//        return self::create(
//            '%s->' . $action . '()',
//            [
//                PlaceholderFactory::pantherClient(),
//            ]
//        );
//    }

//    public static function createCrawlerFilterCall(
//        string $selector,
//        string $variableName = 'variableName'
//    ): StatementInterface {
//        return self::create(
//            $variableName . ' = %s->filter(\'' . $selector . '\')',
//            [
//                new VariablePlaceholder(VariableNames::PANTHER_CRAWLER),
//            ]
//        );
//    }

//    public static function createAssertFalse(string $actual): StatementInterface
//    {
//        return self::create(
//            '%s->assertFalse(' . $actual . ')',
//            [
//                new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
//            ]
//        );
//    }

//    public static function createAssertTrue(string $actual): StatementInterface
//    {
//        return self::create(
//            '%s->assertTrue(' . $actual . ')',
//            [
//                new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
//            ]
//        );
//    }

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
                VariablePlaceholder::createDependency(VariableNames::PHPUNIT_TEST_CASE),
                $methodName,
                [
                    new LiteralExpression($expected),
                    new LiteralExpression($actual)
                ]
            )
        );
    }
}
