<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\Test\TestInterface;

interface FactoryInterface
{
    public static function createFactory(): FactoryInterface;

    /**
     * @param TestInterface $test
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createSource(TestInterface $test): SourceInterface;
}
