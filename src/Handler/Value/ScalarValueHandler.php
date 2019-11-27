<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Value;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedValueException;
use webignition\BasilCompilableSourceFactory\Model\EnvironmentValue;
use webignition\BasilCompilableSourceFactory\ModelFactory\EnvironmentValueFactory;
use webignition\BasilCompilableSourceFactory\ValueTypeIdentifier;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class ScalarValueHandler
{
    private $valueTypeIdentifier;
    private $environmentValueFactory;

    public function __construct(
        ValueTypeIdentifier $valueTypeIdentifier,
        EnvironmentValueFactory $environmentValueFactory
    ) {
        $this->valueTypeIdentifier = $valueTypeIdentifier;
        $this->environmentValueFactory = $environmentValueFactory;
    }

    public static function createHandler(): ScalarValueHandler
    {
        return new ScalarValueHandler(
            new ValueTypeIdentifier(),
            EnvironmentValueFactory::createFactory()
        );
    }

    /**
     * @param string $value
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedValueException
     */
    public function handle(string $value): CodeBlockInterface
    {
        if ($this->valueTypeIdentifier->isBrowserProperty($value)) {
            return $this->handleBrowserProperty();
        }

        if ($this->valueTypeIdentifier->isDataParameter($value)) {
            $property = (string) preg_replace('/^\$data\./', '', $value);

            return new CodeBlock([
                new Statement('$' . $property)
            ]);
        }

        if (EnvironmentValue::is($value)) {
            return $this->handleEnvironmentValue($value);
        }

        if ($this->valueTypeIdentifier->isPageProperty($value)) {
            return $this->handlePageProperty($value);
        }

        if ($this->valueTypeIdentifier->isLiteralValue($value)) {
            return new CodeBlock([
                new Statement((string) $value)
            ]);
        }

        throw new UnsupportedValueException($value);
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

    private function handleEnvironmentValue(string $value): CodeBlockInterface
    {
        $environmentValue = $this->environmentValueFactory->create($value);
        $property = $environmentValue->getProperty();

        $variableDependencies = new VariablePlaceholderCollection();
        $environmentVariableArrayPlaceholder = $variableDependencies->create(
            VariableNames::ENVIRONMENT_VARIABLE_ARRAY
        );

        return new CodeBlock([
            new Statement(
                sprintf(
                    (string) $environmentVariableArrayPlaceholder . '[\'%s\']',
                    $property
                ),
                (new Metadata())->withVariableDependencies($variableDependencies)
            )
        ]);
    }

    /**
     * @param string $value
     *
     * @return CodeBlockInterface
     *
     * @throws UnsupportedValueException
     */
    private function handlePageProperty(string $value): CodeBlockInterface
    {
        $property = (string) preg_replace('/^\$page\./', '', $value);

        $variableDependencies = new VariablePlaceholderCollection();
        $pantherClientPlaceholder = $variableDependencies->create(VariableNames::PANTHER_CLIENT);

        $contentMap = [
            'title' => (string) $pantherClientPlaceholder . '->getTitle()',
            'url' => (string) $pantherClientPlaceholder . '->getCurrentURL()',
        ];

        $statementContent = $contentMap[$property] ?? null;

        if (is_string($statementContent)) {
            $metadata = (new Metadata())
                ->withVariableDependencies($variableDependencies);

            return new CodeBlock([
                new Statement($statementContent, $metadata)
            ]);
        }

        throw new UnsupportedValueException($value);
    }
}
