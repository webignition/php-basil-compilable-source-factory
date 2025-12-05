<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Parser\ActionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromWaitActionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromWaitActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'wait action, literal' => [
                'action' => $actionParser->parse('wait 30'),
                'expectedRenderedSource' => 'usleep(((int) ("30" ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata(),
            ],
            'wait action, element value' => [
                'action' => $actionParser->parse('wait $".duration-selector"'),
                'expectedRenderedSource' => 'usleep(((int) ((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".duration-selector"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return {{ INSPECTOR }}->getValue($element);' . "\n"
                    . '})() ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                ]),
            ],
            'wait action, descendant element value' => [
                'action' => $actionParser->parse('wait $".parent" >> $".child"'),
                'expectedRenderedSource' => 'usleep(((int) ((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".child",' . "\n"
                    . '        "parent": {' . "\n"
                    . '            "locator": ".parent"' . "\n"
                    . '        }' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return {{ INSPECTOR }}->getValue($element);' . "\n"
                    . '})() ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                ]),
            ],
            'wait action, single-character CSS selector element value' => [
                'action' => $actionParser->parse('wait $"a"'),
                'expectedRenderedSource' => 'usleep(((int) ((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": "a"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return {{ INSPECTOR }}->getValue($element);' . "\n"
                    . '})() ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                ]),
            ],
            'wait action, attribute value' => [
                'action' => $actionParser->parse('wait $".duration-selector".attribute_name'),
                'expectedRenderedSource' => 'usleep(((int) ((function () {' . "\n"
                    . '    $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson(\'{' . "\n"
                    . '        "locator": ".duration-selector"' . "\n"
                    . '    }\'));' . "\n"
                    . "\n"
                    . '    return $element->getAttribute(\'attribute_name\');' . "\n"
                    . '})() ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'wait action, browser property' => [
                'action' => $actionParser->parse('wait $browser.size'),
                'expectedRenderedSource' => 'usleep(((int) ((function () {' . "\n"
                    . '    $webDriverDimension = '
                    . '{{ CLIENT }}->getWebDriver()->manage()->window()->getSize();' . "\n"
                    . "\n"
                    . '    return (string) ($webDriverDimension->getWidth()) . \'x\' . '
                    . '(string) ($webDriverDimension->getHeight());' . "\n"
                    . '})() ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
            'wait action, page property' => [
                'action' => $actionParser->parse('wait $page.title'),
                'expectedRenderedSource' => 'usleep(((int) ({{ CLIENT }}->getTitle() ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]),
                ]),
            ],
            'wait action, environment value' => [
                'action' => $actionParser->parse('wait $env.DURATION'),
                'expectedRenderedSource' => 'usleep(((int) ({{ ENV }}[\'DURATION\'] ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                ]),
            ],
            'wait action, environment value with default' => [
                'action' => $actionParser->parse('wait $env.DURATION|"3"'),
                'expectedRenderedSource' => 'usleep(((int) ({{ ENV }}[\'DURATION\'] ?? 3)) * 1000);',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ]),
                ]),
            ],
            'wait action, data parameter' => [
                'action' => $actionParser->parse('wait $data.key'),
                'expectedRenderedSource' => 'usleep(((int) ($key ?? 0)) * 1000);',
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }
}
