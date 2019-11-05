<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\VariablePlaceholder;

class PlaceholderFactory
{
    public static function phpUnitTestCase(): VariablePlaceholder
    {
        return new VariablePlaceholder(VariableNames::PHPUNIT_TEST_CASE);
    }

    public static function pantherClient(): VariablePlaceholder
    {
        return new VariablePlaceholder(VariableNames::PANTHER_CLIENT);
    }

    public static function pantherCrawler(): VariablePlaceholder
    {
        return new VariablePlaceholder(VariableNames::PANTHER_CRAWLER);
    }
}
