<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
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
                'value' => '"value"',
                'expectedContent' => new CodeBlock([
                    new Statement('"value"'),
                ]),
                'expectedMetadata' => new Metadata(),
            ],
            'literal string value: integer' => [
                'value' => '"100"',
                'expectedContent' => new CodeBlock([
                    new Statement('"100"'),
                ]),
                'expectedMetadata' => new Metadata(),
            ],
            'environment parameter value' => [
                'value' => '$env.KEY',
                'expectedContent' => new CodeBlock([
                    new Statement('{{ ENV }}[\'KEY\']'),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::ENVIRONMENT_VARIABLE_ARRAY,
                    ])),
            ],
            'browser property, size' => [
                'value' => '$browser.size',
                'expectedContent' => CodeBlock::fromContent([
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
                'value' => '$page.url',
                'expectedContent' => new CodeBlock([
                    new Statement('{{ CLIENT }}->getCurrentURL()'),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
            ],
            'page property, title' => [
                'value' => '$page.title',
                'expectedContent' => new CodeBlock([
                    new Statement('{{ CLIENT }}->getTitle()'),
                ]),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
            ],
            'data parameter' => [
                'value' => '$data.key',
                'expectedContent' => new CodeBlock([
                    new Statement('$key'),
                ]),
                'expectedMetadata' => new Metadata(),
            ],
        ];
    }
}
