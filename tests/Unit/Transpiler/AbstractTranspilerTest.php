<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Transpiler;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\Transpiler\TranspilerInterface;

abstract class AbstractTranspilerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TranspilerInterface
     */
    protected $transpiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transpiler = $this->createTranspiler();
    }

    abstract protected function createTranspiler(): TranspilerInterface;

    public function testTranspileNonTranspilableModel()
    {
        $this->expectException(NonTranspilableModelException::class);
        $this->expectExceptionMessage('Non-transpilable model "stdClass"');

        $model = new \stdClass();

        $this->transpiler->transpile($model);
    }
}
