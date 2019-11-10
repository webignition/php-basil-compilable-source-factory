<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class WebDriverElementMutatorCallFactory
{
    public static function createFactory(): WebDriverElementMutatorCallFactory
    {
        return new WebDriverElementMutatorCallFactory();
    }

    public function createSetValueCall(
        VariablePlaceholder $collectionPlaceholder,
        VariablePlaceholder $valuePlaceholder
    ): BlockInterface {
        $variableExports = new VariablePlaceholderCollection();
        $variableExports->add($collectionPlaceholder);
        $variableExports->add($valuePlaceholder);

        $variableDependencies = new VariablePlaceholderCollection();
        $mutatorPlaceholder = $variableDependencies->create(VariableNames::WEBDRIVER_ELEMENT_MUTATOR);

        $metadata = new Metadata();
        $metadata->addVariableDependencies($variableDependencies);
        $metadata->addVariableExports($variableExports);

        return new Block([
            new Statement(
                $mutatorPlaceholder . '->setValue(' . $collectionPlaceholder . ', ' . $valuePlaceholder . ')',
                $metadata
            )
        ]);
    }
}
