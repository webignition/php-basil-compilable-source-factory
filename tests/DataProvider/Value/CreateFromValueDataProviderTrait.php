<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Statement;
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
                'expectedContent' => new Statement('"value"'),
                'expectedMetadata' => new Metadata(),
            ],
            'literal string value: integer' => [
                'value' => new LiteralValue('100'),
                'expectedContent' => new Statement('"100"'),
                'expectedMetadata' => new Metadata(),
            ],
            'environment parameter value' => [
                'value' => new ObjectValue(
                    ObjectValueType::ENVIRONMENT_PARAMETER,
                    '$env.KEY',
                    'KEY'
                ),
                'expectedContent' => new Statement('{{ ENV }}[\'KEY\']'),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ])),
            ],
            'browser property, size' => [
                'value' => new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.size', 'size'),
                'expectedContent' => LineList::fromContent([
                    '{{ WEBDRIVER_DIMENSION }} = {{ CLIENT }}->getWebDriver()->manage()->window()->getSize()',
                    '(string) {{ WEBDRIVER_DIMENSION }}->getWidth() . \'x\' . '
                        . '(string) {{ WEBDRIVER_DIMENSION }}->getHeight()',
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]))->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'WEBDRIVER_DIMENSION',
                    ])),
            ],
            'page property, url' => [
                'value' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.url', 'url'),
                'expectedContent' => new Statement('{{ CLIENT }}->getCurrentURL()'),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
            ],
            'page property, title' => [
                'value' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.title', 'title'),
                'expectedContent' => new Statement('{{ CLIENT }}->getTitle()'),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
            ],
            'data parameter' => [
                'value' => new ObjectValue(
                    ObjectValueType::DATA_PARAMETER,
                    '$data.key',
                    'key'
                ),
                'expectedContent' => new Statement('$key'),
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }
}
