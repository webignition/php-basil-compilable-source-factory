<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\ActionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromClickActionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromClickActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        $expectedMetadata = new Metadata(
            classNames: [
                ElementIdentifier::class
            ],
            variableNames: [
                VariableName::DOM_CRAWLER_NAVIGATOR,
                VariableName::PHPUNIT_TEST_CASE,
            ],
        );

        return [
            'interaction action (click), element identifier' => [
                'action' => $actionParser->parse('click $".selector"'),
                'expectedRenderedSource' => '(function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".selector"' . "\n"
                    . '    }\'));' . "\n"
                    . '    $element->click();' . "\n"
                    . '})();' . "\n"
                    . '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => $expectedMetadata,
            ],
            'interaction action (click), parent > child identifier' => [
                'action' => $actionParser->parse('click $".parent" >> $".child"'),
                'expectedRenderedSource' => '(function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".child",' . "\n"
                    . '        "parent": {' . "\n"
                    . '            "locator": ".parent"' . "\n"
                    . '        }' . "\n"
                    . '    }\'));' . "\n"
                    . '    $element->click();' . "\n"
                    . '})();' . "\n"
                    . '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => $expectedMetadata,
            ],
            'interaction action (click), single-character CSS selector element identifier' => [
                'action' => $actionParser->parse('click $"a"'),
                'expectedRenderedSource' => '(function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": "a"' . "\n"
                    . '    }\'));' . "\n"
                    . '    $element->click();' . "\n"
                    . '})();' . "\n"
                    . '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
    }
}
