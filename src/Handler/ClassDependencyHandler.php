<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;

class ClassDependencyHandler implements HandlerInterface
{
    const TEMPLATE = 'use %s';

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
     * @throws UnsupportedModelException
     */
    public function handle(object $model): SourceInterface
    {
        if (!$model instanceof ClassDependency) {
            throw new UnsupportedModelException($model);
        }

        return new Statement(sprintf(self::TEMPLATE, (string) $model));
    }
}
