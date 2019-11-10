<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;

class ScalarValueHandler
{
    private $literalValueHandler;
    private $pagePropertyHandler;

    public function __construct(
        LiteralValueHandler $literalValueHandler,
        PagePropertyHandler $pagePropertyHandler
    ) {
        $this->literalValueHandler = $literalValueHandler;
        $this->pagePropertyHandler = $pagePropertyHandler;
    }

    public static function createHandler(): ScalarValueHandler
    {
        return new ScalarValueHandler(
            new LiteralValueHandler(),
            new PagePropertyHandler()
        );
    }

    /**
     * @param object $model
     *
     * @return BlockInterface
     *
     * @throws UnsupportedModelException
     * @throws UnknownObjectPropertyException
     */
    public function handle(object $model): BlockInterface
    {
        if ($this->isBrowserProperty($model)) {
            return $this->handleBrowserProperty();
        }

        if ($this->isDataParameter($model) &&  $model instanceof ObjectValueInterface) {
            return new Block([
                new Statement('$' . $model->getProperty())
            ]);
        }

        if ($this->isEnvironmentValue($model) && $model instanceof ObjectValueInterface) {
            return $this->handleEnvironmentValue($model);
        }

        if ($this->literalValueHandler->handles($model)) {
            return $this->literalValueHandler->handle($model);
        }

        if ($this->pagePropertyHandler->handles($model)) {
            return $this->pagePropertyHandler->handle($model);
        }

        throw new UnsupportedModelException($model);
    }

    private function isBrowserProperty(object $model): bool
    {
        return $model instanceof ObjectValueInterface
            && ObjectValueType::BROWSER_PROPERTY === $model->getType()
            && 'size' === $model->getProperty();
    }

    private function isDataParameter(object $model): bool
    {
        return $model instanceof ObjectValueInterface && $model->getType() === ObjectValueType::DATA_PARAMETER;
    }

    private function isEnvironmentValue(object $model): bool
    {
        return $model instanceof ObjectValueInterface && ObjectValueType::ENVIRONMENT_PARAMETER === $model->getType();
    }

    private function handleBrowserProperty(): BlockInterface
    {
        $variableExports = new VariablePlaceholderCollection();
        $webDriverDimensionPlaceholder = $variableExports->create('WEBDRIVER_DIMENSION');

        $variableDependencies = new VariablePlaceholderCollection();
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $dimensionAssignment = new Statement(
            sprintf(
                '%s = %s->getWebDriver()->manage()->window()->getSize()',
                $webDriverDimensionPlaceholder,
                $pantherClientPlaceholder
            ),
            (new Metadata())
                ->withVariableDependencies($variableDependencies)
                ->withVariableExports($variableExports)
        );

        $getWidthCall = $webDriverDimensionPlaceholder . '->getWidth()';
        $getHeightCall = $webDriverDimensionPlaceholder . '->getHeight()';

        $dimensionConcatenation = new Statement('(string) ' . $getWidthCall . ' . \'x\' . (string) ' . $getHeightCall);

        return new Block([$dimensionAssignment, $dimensionConcatenation]);
    }

    private function handleEnvironmentValue(ObjectValueInterface $value)
    {
        $variableDependencies = new VariablePlaceholderCollection();
        $environmentVariableArrayPlaceholder = $variableDependencies->create(
            VariableNames::ENVIRONMENT_VARIABLE_ARRAY
        );

        return new Block([
            new Statement(
                sprintf(
                    (string) $environmentVariableArrayPlaceholder . '[\'%s\']',
                    $value->getProperty()
                ),
                (new Metadata())->withVariableDependencies($variableDependencies)
            )
        ]);
    }
}
