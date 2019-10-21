<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\ExecutableCallFactory;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
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
        SourceInterface $accessor,
        string $type,
        array $expectedStatements,
        $expectedAssignedValue
    ) {
        $placeholder = new VariablePlaceholder('VALUE');

        $expectedMetadata = (new Metadata())
            ->withVariableExports(new VariablePlaceholderCollection([
                $placeholder,
            ]));

        $source = $this->factory->createForValueAccessor($accessor, $placeholder, $type);

        $this->assertEquals($expectedStatements, $source->getStatements());
        $this->assertEquals($expectedMetadata, $source->getMetadata());

        $variableIdentifiers = [
            'VALUE' => '$value',
        ];

        $executableCall = $this->executableCallFactory->createWithReturn($source, $variableIdentifiers);

        $assignedValue = eval($executableCall);

        $this->assertSame($expectedAssignedValue, $assignedValue);
    }

    public function createForValueAccessorDataProvider(): array
    {
        return [
            'string value cast to string' => [
                'accessor' => (new Source())->withStatements([
                    '"value"',
                ]),
                'type' => 'string',
                'expectedStatements' => [
                    '{{ VALUE }} = "value" ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                ],
                'expectedAssignedValue' => 'value',
            ],
            'null value cast to string' => [
                'accessor' => (new Source())->withStatements([
                    'null',
                ]),
                'type' => 'string',
                'expectedStatements' => [
                    '{{ VALUE }} = null ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                ],
                'expectedAssignedValue' => '',
            ],
            'int value cast to string' => [
                'accessor' => (new Source())->withStatements([
                    '30',
                ]),
                'type' => 'string',
                'expectedStatements' => [
                    '{{ VALUE }} = 30 ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                ],
                'expectedAssignedValue' => '30',
            ],
            'string value cast to int' => [
                'accessor' => (new Source())->withStatements([
                    '"value"',
                ]),
                'type' => 'int',
                'expectedStatements' => [
                    '{{ VALUE }} = "value" ?? null',
                    '{{ VALUE }} = (int) {{ VALUE }}',
                ],
                'expectedAssignedValue' => 0,
            ],
            'int value cast to int' => [
                'accessor' => (new Source())->withStatements([
                    '30',
                ]),
                'type' => 'int',
                'expectedStatements' => [
                    '{{ VALUE }} = 30 ?? null',
                    '{{ VALUE }} = (int) {{ VALUE }}',
                ],
                'expectedAssignedValue' => 30,
            ],
            'null value cast to int' => [
                'accessor' => (new Source())->withStatements([
                    'null',
                ]),
                'type' => 'int',
                'expectedStatements' => [
                    '{{ VALUE }} = null ?? null',
                    '{{ VALUE }} = (int) {{ VALUE }}',
                ],
                'expectedAssignedValue' => 0,
            ],
        ];
    }
}
