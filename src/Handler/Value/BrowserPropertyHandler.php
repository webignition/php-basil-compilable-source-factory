<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\NonTranspilableModelException;
use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
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
     * @return SourceInterface
     *
     * @throws NonTranspilableModelException
     * @throws UnknownObjectPropertyException
     */
    public function createSource(object $model): SourceInterface
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

        $dimensionAssignment = (new Source())
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

        $dimensionConcatenation = (new Source())
            ->withStatements([
                '(string) ' . $getWidthCall . ' . \'x\' . (string) ' . $getHeightCall,
            ]);

        return (new Source())
            ->withPredecessors([$dimensionAssignment, $dimensionConcatenation]);
    }
}
