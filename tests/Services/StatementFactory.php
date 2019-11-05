<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class StatementFactory
{
    public static function create(string $contentTemplate, array $variableDependencies): StatementInterface
    {
        $content = call_user_func_array('sprintf', array_merge([$contentTemplate], $variableDependencies));

        $metadata = (new Metadata())
            ->withVariableDependencies(new VariablePlaceholderCollection($variableDependencies));

        return new Statement($content, $metadata);
    }

    public static function createAssertBrowserTitle(string $expectedTitle): StatementInterface
    {
        return self::create(
            '%s->assertSame("' . $expectedTitle . '", %s->getTitle())',
            [
                new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
                new VariablePlaceholder(VariableNames::PANTHER_CLIENT),
            ]
        );
    }

    public static function createCrawlerFilterCallForElement(
        string $selector,
        string $variableName = 'variableName'
    ): StatementInterface {
        return self::create(
            $variableName . ' = %s->filter(\'' . $selector . '\')->getElement(0)',
            [
                new VariablePlaceholder(VariableNames::PANTHER_CRAWLER),
            ]
        );
    }

    public static function createAssertSame(string $expected, string $actual): StatementInterface
    {
        return self::create(
            '%s->assertSame(' . $expected . ', ' . $actual . ')',
            [
                new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
            ]
        );
    }

    public static function createCrawlerActionCallForElement(string $selector, string $action): StatementInterface
    {
        return self::create(
            '%s->filter(\'' . $selector . '\')->getElement(0)->' . $action . '()',
            [
                PlaceholderFactory::pantherCrawler(),
            ]
        );
    }

    public static function createClientAction(string $action): StatementInterface
    {
        return self::create(
            '%s->' . $action . '()',
            [
                PlaceholderFactory::pantherClient(),
            ]
        );
    }

    public static function createCrawlerFilterCall(
        string $selector,
        string $variableName = 'variableName'
    ): StatementInterface {
        return self::create(
            $variableName . ' = %s->filter(\'' . $selector . '\')',
            [
                new VariablePlaceholder(VariableNames::PANTHER_CRAWLER),
            ]
        );
    }

    public static function createAssertFalse(string $actual): StatementInterface
    {
        return self::create(
            '%s->assertFalse(' . $actual . ')',
            [
                new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
            ]
        );
    }

    public static function createAssertTrue(string $actual): StatementInterface
    {
        return self::create(
            '%s->assertTrue(' . $actual . ')',
            [
                new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
            ]
        );
    }

    public static function createAssertCount(string $expected, string $actual): StatementInterface
    {
        return self::create(
            '%s->assertCount(' . $expected . ', ' . $actual . ')',
            [
                new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
            ]
        );
    }

    public static function createAssertInstanceOf(string $expected, string $actual): StatementInterface
    {
        return self::create(
            '%s->assertInstanceOf(' . $expected . ', ' . $actual . ')',
            [
                new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE),
            ]
        );
    }
}
