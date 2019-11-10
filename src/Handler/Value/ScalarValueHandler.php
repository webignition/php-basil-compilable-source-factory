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
    private $environmentValueHandler;
    private $literalValueHandler;
    private $pagePropertyHandler;

    public function __construct(
        EnvironmentValueHandler $environmentValueHandler,
        LiteralValueHandler $literalValueHandler,
        PagePropertyHandler $pagePropertyHandler
    ) {
        $this->environmentValueHandler = $environmentValueHandler;
        $this->literalValueHandler = $literalValueHandler;
        $this->pagePropertyHandler = $pagePropertyHandler;
    }

    public static function createHandler(): ScalarValueHandler
    {
        return new ScalarValueHandler(
            new EnvironmentValueHandler(),
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
        $isBrowserProperty = $model instanceof ObjectValueInterface
            && ObjectValueType::BROWSER_PROPERTY === $model->getType()
            && 'size' === $model->getProperty();

        if ($isBrowserProperty) {
            return $this->handleFoo();
        }

        if ($model instanceof ObjectValueInterface && $model->getType() === ObjectValueType::DATA_PARAMETER) {
            return new Block([
                new Statement('$' . $model->getProperty())
            ]);
        }

        if ($this->environmentValueHandler->handles($model)) {
            return $this->environmentValueHandler->handle($model);
        }

        if ($this->literalValueHandler->handles($model)) {
            return $this->literalValueHandler->handle($model);
        }

        if ($this->pagePropertyHandler->handles($model)) {
            return $this->pagePropertyHandler->handle($model);
        }

        throw new UnsupportedModelException($model);
    }

    private function handleFoo()
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
}
