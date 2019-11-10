<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnknownObjectPropertyException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedModelException;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Value\LiteralValueInterface;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;

class ScalarValueHandler
{
    /**
     * @param ValueInterface $value
     *
     * @return CodeBlock
     *
     * @throws UnsupportedModelException
     * @throws UnknownObjectPropertyException
     */
    public function handle(ValueInterface $value): CodeBlockInterface
    {
        if ($value instanceof ObjectValueInterface) {
            if ($this->isBrowserProperty($value)) {
                return $this->handleBrowserProperty();
            }

            if ($this->isDataParameter($value)) {
                return new CodeBlock([
                    new Statement('$' . $value->getProperty())
                ]);
            }

            if ($this->isEnvironmentValue($value)) {
                return $this->handleEnvironmentValue($value);
            }

            if ($this->isPageProperty($value)) {
                return $this->handlePageProperty($value);
            }
        }

        if ($this->isLiteralValue($value)) {
            return new CodeBlock([
                new Statement((string) $value)
            ]);
        }

        throw new UnsupportedModelException($value);
    }

    private function isBrowserProperty(ObjectValueInterface $value): bool
    {
        return ObjectValueType::BROWSER_PROPERTY === $value->getType() && 'size' === $value->getProperty();
    }

    private function isDataParameter(ObjectValueInterface $value): bool
    {
        return $value->getType() === ObjectValueType::DATA_PARAMETER;
    }

    private function isEnvironmentValue(ObjectValueInterface $value): bool
    {
        return ObjectValueType::ENVIRONMENT_PARAMETER === $value->getType();
    }

    private function isLiteralValue(ValueInterface $value): bool
    {
        return $value instanceof LiteralValueInterface;
    }

    private function isPageProperty(ObjectValueInterface $value): bool
    {
        return ObjectValueType::PAGE_PROPERTY === $value->getType();
    }

    private function handleBrowserProperty(): CodeBlockInterface
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

        return new CodeBlock([$dimensionAssignment, $dimensionConcatenation]);
    }

    private function handleEnvironmentValue(ObjectValueInterface $value): CodeBlockInterface
    {
        $variableDependencies = new VariablePlaceholderCollection();
        $environmentVariableArrayPlaceholder = $variableDependencies->create(
            VariableNames::ENVIRONMENT_VARIABLE_ARRAY
        );

        return new CodeBlock([
            new Statement(
                sprintf(
                    (string) $environmentVariableArrayPlaceholder . '[\'%s\']',
                    $value->getProperty()
                ),
                (new Metadata())->withVariableDependencies($variableDependencies)
            )
        ]);
    }

    /**
     * @param ObjectValueInterface $value
     *
     * @return BlockInterface
     *
     * @throws UnknownObjectPropertyException
     */
    private function handlePageProperty(ObjectValueInterface $value): CodeBlockInterface
    {
        $variableDependencies = new VariablePlaceholderCollection();
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $contentMap = [
            'title' => (string) $pantherClientPlaceholder . '->getTitle()',
            'url' => (string) $pantherClientPlaceholder . '->getCurrentURL()',
        ];

        $statementContent = $contentMap[$value->getProperty()] ?? null;

        if (is_string($statementContent)) {
            $metadata = (new Metadata())
                ->withVariableDependencies($variableDependencies);

            return new CodeBlock([
                new Statement($statementContent, $metadata)
            ]);
        }

        throw new UnknownObjectPropertyException($value);
    }
}
