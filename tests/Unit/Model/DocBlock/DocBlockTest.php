<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\DocBlock;

use webignition\BasilCompilableSourceFactory\Model\Annotation\ParameterAnnotation;
use webignition\BasilCompilableSourceFactory\Model\DocBlock\DocBlock;
use webignition\BasilCompilableSourceFactory\Model\VariableName;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class DocBlockTest extends AbstractResolvableTestCase
{
    /**
     * @dataProvider mergeDataProvider
     * @dataProvider appendDataProvider
     */
    public function testAppend(DocBlock $docBlock, DocBlock $merge, DocBlock $expectedDocBlock): void
    {
        $this->assertEquals($expectedDocBlock, $docBlock->append($merge));
    }

    /**
     * @return array<mixed>
     */
    public function appendDataProvider(): array
    {
        return [
            'append: non-empty, non-empty' => [
                'docBlock' => new DocBlock([
                    'docBlock line',
                ]),
                'merge' => new DocBlock([
                    'merge line',
                ]),
                'expectedDocBlock' => new DocBlock([
                    'docBlock line',
                    'merge line',
                ]),
            ],
        ];
    }

    /**
     * @dataProvider mergeDataProvider
     * @dataProvider prependDataProvider
     */
    public function testPrepend(DocBlock $docBlock, DocBlock $merge, DocBlock $expectedDocBlock): void
    {
        $this->assertEquals($expectedDocBlock, $docBlock->prepend($merge));
    }

    /**
     * @return array<mixed>
     */
    public function prependDataProvider(): array
    {
        return [
            'prepend: non-empty, non-empty' => [
                'docBlock' => new DocBlock([
                    'docBlock line',
                ]),
                'merge' => new DocBlock([
                    'merge line',
                ]),
                'expectedDocBlock' => new DocBlock([
                    'merge line',
                    'docBlock line',
                ]),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function mergeDataProvider(): array
    {
        return [
            'merge: empty, empty' => [
                'docBlock' => new DocBlock([]),
                'merge' => new DocBlock([]),
                'expectedDocBlock' => new DocBlock([]),
            ],
            'merge: non-empty, empty' => [
                'docBlock' => new DocBlock([
                    'docBlock line',
                ]),
                'merge' => new DocBlock([]),
                'expectedDocBlock' => new DocBlock([
                    'docBlock line',
                ]),
            ],
            'merge: empty, non-empty' => [
                'docBlock' => new DocBlock([]),
                'merge' => new DocBlock([
                    'merge line',
                ]),
                'expectedDocBlock' => new DocBlock([
                    'merge line',
                ]),
            ],
        ];
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender(DocBlock $docBlock, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $docBlock);
    }

    /**
     * @return array<mixed>
     */
    public function renderDataProvider(): array
    {
        return [
            'empty' => [
                'docBlock' => new DocBlock([]),
                'expectedString' => '/**' . "\n"
                    . ' */',
            ],
            'non-empty' => [
                'docBlock' => new DocBlock([
                    "\n",
                    'single line comment',
                    new ParameterAnnotation('string', new VariableName('name'))
                ]),
                'expectedString' => '/**' . "\n"
                    . ' *' . "\n"
                    . ' * single line comment' . "\n"
                    . ' * @param string $name' . "\n"
                    . ' */',
            ],
        ];
    }
}
