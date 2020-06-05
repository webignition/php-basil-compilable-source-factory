<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\ResolvablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilParser\ActionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromSubmitActionDataProviderTrait
{
    public function createFromSubmitActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'interaction action (submit), element identifier' => [
                'action' => $actionParser->parse('submit $".selector"'),
                'expectedRenderedSource' =>
                    '(function () {' . "\n" .
                    '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n" .
                    '        "locator": ".selector"' . "\n" .
                    '    }\'));' . "\n" .
                    '    $element->submit();' . "\n" .
                    '})();' . "\n" .
                    '{{ PHPUNIT }}->refreshCrawlerAndNavigator();',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(ElementIdentifier::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => ResolvablePlaceholderCollection::createDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }
}
