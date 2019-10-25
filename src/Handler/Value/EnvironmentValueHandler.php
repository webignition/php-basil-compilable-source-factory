<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;

class EnvironmentValueHandler implements HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new EnvironmentValueHandler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof ObjectValueInterface && ObjectValueType::ENVIRONMENT_PARAMETER === $model->getType();
    }

    /**
     * @param object $model
     *
     * @return SourceInterface
     *
     * @throws UnsupportedModelException
     */
    public function createSource(object $model): SourceInterface
    {
        if ($this->handles($model) && $model instanceof ObjectValueInterface) {
            $variableDependencies = new VariablePlaceholderCollection();
            $environmentVariableArrayPlaceholder = $variableDependencies->create(
                VariableNames::ENVIRONMENT_VARIABLE_ARRAY
            );

            return new Statement(
                sprintf(
                    (string) $environmentVariableArrayPlaceholder . '[\'%s\']',
                    $model->getProperty()
                ),
                (new Metadata())->withVariableDependencies($variableDependencies)
            );
        }

        throw new UnsupportedModelException($model);
    }
}
