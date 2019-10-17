<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\Transpiler\TranspilerInterface;
use webignition\BasilCompilationSource\SourceInterface;

class Factory implements FactoryInterface
{
    /**
     * @var TranspilerInterface[]
     */
    protected $transpilers;

    public function __construct(array $transpilers = [])
    {
        $this->transpilers = [];

        foreach ($transpilers as $transpiler) {
            if ($transpiler instanceof TranspilerInterface) {
                $this->transpilers[] = $transpiler;
            }
        }
    }

    public static function createFactory(): FactoryInterface
    {
        return new Factory();
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createSource(object $model): SourceInterface
    {
        $transpiler = $this->findTranspiler($model);

        if ($transpiler instanceof TranspilerInterface) {
            return $transpiler->transpile($model);
        }

        throw new NonTranspilableModelException($model);
    }

    protected function findTranspiler(object $model): ?TranspilerInterface
    {
        foreach ($this->transpilers as $transpiler) {
            if ($transpiler->handles($model)) {
                return $transpiler;
            }
        }

        return null;
    }
}
