<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Handler\ClassDependencyHandler;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\Metadata;

class ClassDependencyHandlerTest extends AbstractHandlerTest
{
    protected function createHandler(): HandlerInterface
    {
        return ClassDependencyHandler::createHandler();
    }

    public function testHandlesDoesHandle()
    {
        $model = new ClassDependency(ClassDependency::class);

        $this->assertTrue($this->handler->handles($model));
    }

    public function testHandlesDoesNotHandle()
    {
        $model = new \stdClass();

        $this->assertFalse($this->handler->handles($model));
    }

    /**
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(
        ClassDependency $classDependency,
        array $expectedSerializedData
    ) {
        $source = $this->handler->createSource($classDependency);

        $this->assertJsonSerializedData($expectedSerializedData, $source);
        $this->assertEquals(new Metadata(), $source->getMetadata());
    }

    public function createSourceDataProvider(): array
    {
        return [
            'without alias' => [
                'classDependency' => new ClassDependency(ClassDependency::class),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => 'use webignition\BasilCompilationSource\ClassDependency',
                        ],
                    ],
                ]
            ],
            'with alias' => [
                'classDependency' => new ClassDependency(ClassDependency::class, 'CD'),
                'expectedSerializedData' => [
                    'type' => 'line-list',
                    'lines' => [
                        [
                            'type' => 'statement',
                            'content' => 'use webignition\BasilCompilationSource\ClassDependency as CD',
                        ],
                    ],
                ]
            ],
        ];
    }
}
