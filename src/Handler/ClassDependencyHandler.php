<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;

class ClassDependencyHandler implements HandlerInterface
{
    const CLASS_NAME_ONLY_TEMPLATE = 'use %s';
    const WITH_ALIAS_TEMPLATE = self::CLASS_NAME_ONLY_TEMPLATE . ' as %s';

    public static function createHandler(): HandlerInterface
    {
        return new ClassDependencyHandler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof ClassDependency;
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
        if (!$model instanceof ClassDependency) {
            throw new NonTranspilableModelException($model);
        }

        $alias = $model->getAlias();

        $content = null === $alias
            ? sprintf(self::CLASS_NAME_ONLY_TEMPLATE, $model->getClassName())
            : sprintf(self::WITH_ALIAS_TEMPLATE, $model->getClassName(), $model->getAlias());

        return new Statement($content);
    }
}
