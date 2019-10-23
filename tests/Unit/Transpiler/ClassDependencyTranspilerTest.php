<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Transpiler;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\ClassDependencyTranspiler;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\Metadata;

class ClassDependencyTranspilerTest extends AbstractTranspilerTest
{
    protected function createTranspiler(): HandlerInterface
    {
        return ClassDependencyTranspiler::createHandler();
    }

    public function testHandlesDoesHandle()
    {
        $model = new ClassDependency(ClassDependency::class);

        $this->assertTrue($this->transpiler->handles($model));
    }

    public function testHandlesDoesNotHandle()
    {
        $model = new \stdClass();

        $this->assertFalse($this->transpiler->handles($model));
    }

    /**
     * @dataProvider transpileDataProvider
     */
    public function testTranspile(
        ClassDependency $classDependency,
        array $expectedStatements
    ) {
        $source = $this->transpiler->createSource($classDependency);

        $this->assertEquals($expectedStatements, $source->getStatements());
        $this->assertEquals(new Metadata(), $source->getMetadata());
    }

    public function transpileDataProvider(): array
    {
        return [
            'without alias' => [
                'classDependency' => new ClassDependency(ClassDependency::class),
                'expectedStatements' => [
                    'use webignition\BasilCompilationSource\ClassDependency',
                ]
            ],
            'with alias' => [
                'classDependency' => new ClassDependency(ClassDependency::class, 'CD'),
                'expectedStatements' => [
                    'use webignition\BasilCompilationSource\ClassDependency as CD',
                ]
            ],
        ];
    }
}
