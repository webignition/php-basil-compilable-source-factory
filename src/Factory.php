<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilModel\Test\TestInterface;

class Factory implements FactoryInterface
{
    public static function createFactory(): FactoryInterface
    {
        return new Factory();
    }

    /**
     * @param TestInterface $test
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createSource(TestInterface $test): SourceInterface
    {
        throw new NonTranspilableModelException($test);
    }
}
