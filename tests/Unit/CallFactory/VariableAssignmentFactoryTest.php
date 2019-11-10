<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCodeGenerator\BlockGenerator;
use webignition\BasilCompilableSourceFactory\CallFactory\VariableAssignmentFactory;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class VariableAssignmentFactoryTest extends AbstractTestCase
{
    /**
     * @var VariableAssignmentFactory
     */
    private $factory;

    /**
     * @var BlockGenerator
     */
    private $blockGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = VariableAssignmentFactory::createFactory();
        $this->blockGenerator = BlockGenerator::create();
    }

    /**
     * @dataProvider createForValueAccessorDataProvider
     */
    public function testCreateForValueAccessor(
        BlockInterface $accessor,
        string $type,
        BlockInterface $expectedContent,
        $expectedAssignedValue
    ) {
        $placeholder = new VariablePlaceholder('VALUE');

        $expectedMetadata = (new Metadata())
            ->withVariableExports(new VariablePlaceholderCollection([
                $placeholder,
            ]));

        $source = $this->factory->createForValueAccessor($accessor, $placeholder, $type);

        $this->assertSourceContentEquals($expectedContent, $source);
        $this->assertMetadataEquals($expectedMetadata, $source->getMetadata());

        if ($source instanceof Block) {
            $source->mutateLastStatement(function ($content) {
                return 'return ' . $content;
            });

            $code = $this->blockGenerator->createFromBlock($source, [
                'VALUE' => '$value',
            ]);

            $assignedValue = eval($code);

            $this->assertSame($expectedAssignedValue, $assignedValue);
        }
    }

    public function createForValueAccessorDataProvider(): array
    {
        return [
            'string value cast to string' => [
                'accessor' => new Block([
                    new Statement('"value"'),
                ]),
                'type' => 'string',
                'expectedContent' => Block::fromContent([
                    '{{ VALUE }} = "value" ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                ]),
                'expectedAssignedValue' => 'value',
            ],
            'null value cast to string' => [
                'accessor' => new Block([
                    new Statement('null'),
                ]),
                'type' => 'string',
                'expectedContent' => Block::fromContent([
                    '{{ VALUE }} = null ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                ]),
                'expectedAssignedValue' => '',
            ],
            'int value cast to string' => [
                'accessor' => new Block([
                    new Statement('30'),
                ]),
                'type' => 'string',
                'expectedContent' => Block::fromContent([
                    '{{ VALUE }} = 30 ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                ]),
                'expectedAssignedValue' => '30',
            ],
            'string value cast to int' => [
                'accessor' => new Block([
                    new Statement('"value"'),
                ]),
                'type' => 'int',
                'expectedContent' => Block::fromContent([
                    '{{ VALUE }} = "value" ?? null',
                    '{{ VALUE }} = (int) {{ VALUE }}',
                ]),
                'expectedAssignedValue' => 0,
            ],
            'int value cast to int' => [
                'accessor' => new Block([
                    new Statement('30'),
                ]),
                'type' => 'int',
                'expectedContent' => Block::fromContent([
                    '{{ VALUE }} = 30 ?? null',
                    '{{ VALUE }} = (int) {{ VALUE }}',
                ]),
                'expectedAssignedValue' => 30,
            ],
            'null value cast to int' => [
                'accessor' => new Block([
                    new Statement('null'),
                ]),
                'type' => 'int',
                'expectedContent' => Block::fromContent([
                    '{{ VALUE }} = null ?? null',
                    '{{ VALUE }} = (int) {{ VALUE }}',
                ]),
                'expectedAssignedValue' => 0,
            ],
            'only last statement is modified' => [
                'accessor' => Block::fromContent([
                    '$a = "content"',
                    '$b = $a',
                    '$b',
                ]),
                'type' => 'string',
                'expectedContent' => Block::fromContent([
                    '$a = "content"',
                    '$b = $a',
                    '{{ VALUE }} = $b ?? null',
                    '{{ VALUE }} = (string) {{ VALUE }}',
                ]),
                'expectedAssignedValue' => 'content',
            ],
        ];
    }
}
