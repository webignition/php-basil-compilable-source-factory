<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Transpiler;

use webignition\BasilCompilableSourceFactory\Transpiler\ClassDependencyTranspiler;
use webignition\BasilCompilableSourceFactory\Transpiler\TranspilerInterface;
use webignition\BasilCompilationSource\ClassDependency;

class ClassDependencyTranspilerTest extends AbstractTranspilerTest
{
    protected function createTranspiler(): TranspilerInterface
    {
        return ClassDependencyTranspiler::createTranspiler();
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
}
