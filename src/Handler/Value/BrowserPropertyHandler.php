<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\StatementListInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;

class BrowserPropertyHandler implements HandlerInterface
{
    const PROPERTY_NAME_SIZE = 'size';

    public static function createHandler(): HandlerInterface
    {
        return new BrowserPropertyHandler();
    }

    public function handles(object $model): bool
    {
        return $model instanceof ObjectValueInterface && ObjectValueType::BROWSER_PROPERTY === $model->getType();
    }

    /**
     * @param object $model
     *
     * @return StatementListInterface
     *
     * @throws NonTranspilableModelException
     * @throws UnknownObjectPropertyException
     */
    public function createStatementList(object $model): StatementListInterface
    {
        if (!$this->handles($model) || !$model instanceof ObjectValueInterface) {
            throw new NonTranspilableModelException($model);
        }

        $property = $model->getProperty();
        if (self::PROPERTY_NAME_SIZE !== $property) {
            throw new UnknownObjectPropertyException($model);
        }

        $variableExports = new VariablePlaceholderCollection();
        $webDriverDimensionPlaceholder = $variableExports->create('WEBDRIVER_DIMENSION');

        $variableDependencies = new VariablePlaceholderCollection();
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $dimensionAssignment = (new StatementList())
            ->withStatements([
                sprintf(
                    '%s = %s->getWebDriver()->manage()->window()->getSize()',
                    $webDriverDimensionPlaceholder,
                    $pantherClientPlaceholder
                ),
            ])
            ->withMetadata(
                (new Metadata())
                ->withVariableDependencies($variableDependencies)
                ->withVariableExports($variableExports)
            );

        $getWidthCall = $webDriverDimensionPlaceholder . '->getWidth()';
        $getHeightCall = $webDriverDimensionPlaceholder . '->getHeight()';

        $dimensionConcatenation = (new StatementList())
            ->withStatements([
                '(string) ' . $getWidthCall . ' . \'x\' . (string) ' . $getHeightCall,
            ]);

        return (new StatementList())
            ->withPredecessors([$dimensionAssignment, $dimensionConcatenation]);
    }
}
