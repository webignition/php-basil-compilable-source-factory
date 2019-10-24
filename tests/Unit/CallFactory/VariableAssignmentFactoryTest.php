<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\ExecutableCallFactory;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\StatementListInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class VariableAssignmentFactoryTest extends \PHPUnit\Framework\TestCase
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
        StatementListInterface $accessor,
        string $type,
        array $expectedStatements,
        $expectedAssignedValue
    ) {
        $placeholder = new VariablePlaceholder('VALUE');

        $expectedMetadata = (new Metadata())
            ->withVariableExports(new VariablePlaceholderCollection([
                $placeholder,
            ]));

        $statementList = $this->factory->createForValueAccessor($accessor, $placeholder, $type);

        $this->assertEquals($expectedStatements, $statementList->getStatements());
        $this->assertEquals($expectedMetadata, $statementList->getMetadata());

        $variableIdentifiers = [
            'VALUE' => '$value',
        ];

        $executableCall = $this->executableCallFactory->createWithReturn($statementList, $variableIdentifiers);

        $assignedValue = eval($executableCall);

        $this->assertSame($expectedAssignedValue, $assignedValue);
    }

    public function createForValueAccessorDataProvider(): array
    {
        return [
            'string value cast to string' => [
                'accessor' => new StatementList([
                    new Statement('"value"'),
                ]),
                'type' => 'string',
                'expectedStatements' => [
                    '{{ VALUE }} = "value" ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                ],
                'expectedAssignedValue' => 'value',
            ],
            'null value cast to string' => [
                'accessor' => new StatementList([
                    new Statement('null'),
                ]),
                'type' => 'string',
                'expectedStatements' => [
                    '{{ VALUE }} = null ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                ],
                'expectedAssignedValue' => '',
            ],
            'int value cast to string' => [
                'accessor' => new StatementList([
                    new Statement('30'),
                ]),
                'type' => 'string',
                'expectedStatements' => [
                    '{{ VALUE }} = 30 ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                ],
                'expectedAssignedValue' => '30',
            ],
            'string value cast to int' => [
                'accessor' => new StatementList([
                    new Statement('"value"'),
                ]),
                'type' => 'int',
                'expectedStatements' => [
                    '{{ VALUE }} = "value" ?? null',
                    '{{ VALUE }} = (int) {{ VALUE }}',
                ],
                'expectedAssignedValue' => 0,
            ],
            'int value cast to int' => [
                'accessor' => new StatementList([
                    new Statement('30'),
                ]),
                'type' => 'int',
                'expectedStatements' => [
                    '{{ VALUE }} = 30 ?? null',
                    '{{ VALUE }} = (int) {{ VALUE }}',
                ],
                'expectedAssignedValue' => 30,
            ],
            'null value cast to int' => [
                'accessor' => new StatementList([
                    new Statement('null'),
                ]),
                'type' => 'int',
                'expectedStatements' => [
                    '{{ VALUE }} = null ?? null',
                    '{{ VALUE }} = (int) {{ VALUE }}',
                ],
                'expectedAssignedValue' => 0,
            ],
            'only last statement is modified' => [
                'accessor' => new StatementList([
                    new Statement('$a = "content"'),
                    new Statement('$b = $a'),
                    new Statement('$c = $b'),
                    new Statement('$c'),
                ]),
                'type' => 'string',
                'expectedStatements' => [
                    '$a = "content"',
                    '$b = $a',
                    '$c = $b',
                    '{{ VALUE }} = $c ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                ],
                'expectedAssignedValue' => 'content',
            ],
        ];
    }
}
