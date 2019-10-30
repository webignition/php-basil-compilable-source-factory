<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Handler\ClassDependencyHandler;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementInterface;

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
        StatementInterface $expectedStatement
    ) {
        $source = $this->handler->createSource($classDependency);

        $this->assertEquals($expectedStatement, $source);
    }

    public function createSourceDataProvider(): array
    {
        return [
            'without alias' => [
                'classDependency' => new ClassDependency(ClassDependency::class),
                'expectedStatement' => new Statement('use webignition\BasilCompilationSource\ClassDependency'),
            ],
            'with alias' => [
                'classDependency' => new ClassDependency(ClassDependency::class, 'CD'),
                'expectedStatement' => new Statement('use webignition\BasilCompilationSource\ClassDependency as CD'),
            ],
        ];
    }
}
