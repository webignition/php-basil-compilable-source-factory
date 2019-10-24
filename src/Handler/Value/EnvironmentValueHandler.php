<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\StatementListInterface;
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
     * @return StatementListInterface
     *
     * @throws NonTranspilableModelException
     */
    public function createStatementList(object $model): StatementListInterface
    {
        if ($this->handles($model) && $model instanceof ObjectValueInterface) {
            $variableDependencies = new VariablePlaceholderCollection();
            $environmentVariableArrayPlaceholder = $variableDependencies->create(
                VariableNames::ENVIRONMENT_VARIABLE_ARRAY
            );

            $statementContent = sprintf(
                (string) $environmentVariableArrayPlaceholder . '[\'%s\']',
                $model->getProperty()
            );

            $metadata = (new Metadata())->withVariableDependencies($variableDependencies);

            return new StatementList([
                new Statement($statementContent, $metadata),
            ]);
        }

        throw new NonTranspilableModelException($model);
    }
}
