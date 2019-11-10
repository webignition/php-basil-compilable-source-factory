<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class WebDriverElementInspectorCallFactory
{
    public static function createFactory(): WebDriverElementInspectorCallFactory
    {
        return new WebDriverElementInspectorCallFactory();
    }

    public function createGetValueCall(VariablePlaceholder $collectionPlaceholder): CodeBlockInterface
    {
        $variableExports = new VariablePlaceholderCollection();
        $variableExports->add($collectionPlaceholder);

        $variableDependencies = new VariablePlaceholderCollection();
        $inspectorPlaceholder = $variableDependencies->create(VariableNames::WEBDRIVER_ELEMENT_INSPECTOR);

        $metadata = new Metadata();
        $metadata->addVariableDependencies($variableDependencies);
        $metadata->addVariableExports($variableExports);

        return new CodeBlock([
            new Statement($inspectorPlaceholder . '->getValue(' . $collectionPlaceholder . ')', $metadata)
        ]);
    }
}
