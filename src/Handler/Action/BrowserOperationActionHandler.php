<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\StatementListInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\NoArgumentsAction;

class BrowserOperationActionHandler implements HandlerInterface
{
    const HANDLED_ACTION_TYPES = [
        ActionTypes::BACK,
        ActionTypes::FORWARD,
        ActionTypes::RELOAD,
    ];

    public static function createHandler(): HandlerInterface
    {
        return new BrowserOperationActionHandler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof NoArgumentsAction && in_array($model->getType(), self::HANDLED_ACTION_TYPES);
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
        if (!$model instanceof NoArgumentsAction) {
            throw new NonTranspilableModelException($model);
        }

        if (!in_array($model->getType(), self::HANDLED_ACTION_TYPES)) {
            throw new NonTranspilableModelException($model);
        }

        $variableDependencies = new VariablePlaceholderCollection();
        $pantherCrawlerPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CRAWLER);
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $metadata = (new Metadata())->withVariableDependencies($variableDependencies);

        return (new StatementList())
            ->withStatements([
                sprintf(
                    '%s = %s->%s()',
                    $pantherCrawlerPlaceholder,
                    $pantherClientPlaceholder,
                    $model->getType()
                ),
            ])
            ->withMetadata($metadata);
    }
}
