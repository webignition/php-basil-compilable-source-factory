<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\ExecutableCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class VariableAssignmentFactoryTest extends AbstractTestCase
{
    /**
     * @var VariableAssignmentFactory
     */
    private $factory;

    /**
     * @var ExecutableCallFactory
     */
    private $executableCallFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = VariableAssignmentFactory::createFactory();
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }

    /**
     * @dataProvider createForValueAccessorDataProvider
     */
    public function testCreateForValueAccessor(
        SourceInterface $accessor,
        string $type,
        array $expectedSerializedData,
        $expectedAssignedValue
    ) {
        $placeholder = new VariablePlaceholder('VALUE');

        $expectedMetadata = (new Metadata())
            ->withVariableExports(new VariablePlaceholderCollection([
                $placeholder,
            ]));

        $lineList = $this->factory->createForValueAccessor($accessor, $placeholder, $type);

        $this->assertJsonSerializedData($expectedSerializedData, $lineList);
        $this->assertMetadataEquals($expectedMetadata, $lineList->getMetadata());

        $variableIdentifiers = [
            'VALUE' => '$value',
        ];

        $executableCall = $this->executableCallFactory->createWithReturn($lineList, $variableIdentifiers);

        $assignedValue = eval($executableCall);

        $this->assertSame($expectedAssignedValue, $assignedValue);
    }

    public function createForValueAccessorDataProvider(): array
    {
        return [
            'string value cast to string' => [
                'accessor' => new LineList([
                    new Statement('"value"'),
                ]),
                'type' => 'string',
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = "value" ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = (string) {{ VALUE }}',
                        ],
                    ],
                ],
                'expectedAssignedValue' => 'value',
            ],
            'null value cast to string' => [
                'accessor' => new LineList([
                    new Statement('null'),
                ]),
                'type' => 'string',
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = null ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = (string) {{ VALUE }}',
                        ],
                    ],
                ],
                'expectedAssignedValue' => '',
            ],
            'int value cast to string' => [
                'accessor' => new LineList([
                    new Statement('30'),
                ]),
                'type' => 'string',
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = 30 ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = (string) {{ VALUE }}',
                        ],
                    ],
                ],
                'expectedAssignedValue' => '30',
            ],
            'string value cast to int' => [
                'accessor' => new LineList([
                    new Statement('"value"'),
                ]),
                'type' => 'int',
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = "value" ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = (int) {{ VALUE }}',
                        ],
                    ],
                ],
                'expectedAssignedValue' => 0,
            ],
            'int value cast to int' => [
                'accessor' => new LineList([
                    new Statement('30'),
                ]),
                'type' => 'int',
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = 30 ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = (int) {{ VALUE }}',
                        ],
                    ],
                ],
                'expectedAssignedValue' => 30,
            ],
            'null value cast to int' => [
                'accessor' => new LineList([
                    new Statement('null'),
                ]),
                'type' => 'int',
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = null ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = (int) {{ VALUE }}',
                        ],
                    ],
                ],
                'expectedAssignedValue' => 0,
            ],
            'only last statement is modified' => [
                'accessor' => new LineList([
                    new Statement('$a = "content"'),
                    new Statement('$b = $a'),
                    new Statement('$c = $b'),
                    new Statement('$c'),
                ]),
                'type' => 'string',
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => '$a = "content"',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '$b = $a',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '$c = $b',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = $c ?? null',
                        ],
                        [
                            'type' => 'statement',
                            'content' => '{{ VALUE }} = (string) {{ VALUE }}',
                        ],
                    ],
                ],
                'expectedAssignedValue' => 'content',
            ],
        ];
    }
}
