<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
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

    public static function createHandler(): BrowserOperationActionHandler
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
     * @return BlockInterface
     *
     * @throws UnsupportedModelException
     */
    public function handle(object $model): BlockInterface
    {
        if (!$model instanceof NoArgumentsAction) {
            throw new UnsupportedModelException($model);
        }

        if (!in_array($model->getType(), self::HANDLED_ACTION_TYPES)) {
            throw new UnsupportedModelException($model);
        }

        $variableDependencies = new VariablePlaceholderCollection();
        $pantherCrawlerPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CRAWLER);
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $metadata = (new Metadata())->withVariableDependencies($variableDependencies);

        return new Block([
            new Statement(
                sprintf(
                    '%s = %s->%s()',
                    $pantherCrawlerPlaceholder,
                    $pantherClientPlaceholder,
                    $model->getType()
                ),
                $metadata
            )
        ]);
    }
}
