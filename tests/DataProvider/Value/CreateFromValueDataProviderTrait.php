<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;

trait CreateFromValueDataProviderTrait
{
    public function createFromValueDataProvider(): array
    {
        return [
            'literal string value: string' => [
                'value' => new LiteralValue('value'),
                'expectedSerializedData' => [
                    'type' => 'statement',
                    'content' => '"value"',
                ],
                'expectedMetadata' => new Metadata(),
            ],
            'literal string value: integer' => [
                'value' => new LiteralValue('100'),
                'expectedSerializedData' => [
                    'type' => 'statement',
                    'content' => '"100"',
                ],
                'expectedMetadata' => new Metadata(),
            ],
            'environment parameter value' => [
                'value' => new ObjectValue(
                    ObjectValueType::ENVIRONMENT_PARAMETER,
                    '$env.KEY',
                    'KEY'
                ),
                'expectedSerializedData' => [
                    'type' => 'statement',
                    'content' => '{{ ENVIRONMENT_VARIABLE_ARRAY }}[\'KEY\']',
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ])),
            ],
            'browser property, size' => [
                'value' => new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.size', 'size'),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ WEBDRIVER_DIMENSION }} = '
                                . '{{ PANTHER_CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                                . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight()',
                        ],
                    ],
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]))->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'WEBDRIVER_DIMENSION',
                    ])),
            ],
            'page property, url' => [
                'value' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.url', 'url'),
                'expectedSerializedData' => [
                    'type' => 'statement',
                    'content' => '{{ PANTHER_CLIENT }}->getCurrentURL()',
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
            ],
            'page property, title' => [
                'value' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.title', 'title'),
                'expectedSerializedData' => [
                    'type' => 'statement',
                    'content' => '{{ PANTHER_CLIENT }}->getTitle()',
                ],
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
            ],
        ];
    }
}
