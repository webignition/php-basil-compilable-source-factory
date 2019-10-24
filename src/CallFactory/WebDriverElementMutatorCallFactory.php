<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementInterface;
use webignition\BasilCompilationSource\Metadata;
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
    ): StatementInterface {
        $variableExports = new VariablePlaceholderCollection();
        $variableExports = $variableExports->withAdditionalItems([$collectionPlaceholder, $valuePlaceholder]);

        $variableDependencies = new VariablePlaceholderCollection();
        $mutatorPlaceholder = $variableDependencies->create(VariableNames::WEBDRIVER_ELEMENT_MUTATOR);

        $metadata = new Metadata();
        $metadata->addVariableDependencies($variableDependencies);
        $metadata->addVariableExports($variableExports);

        return new Statement(
            $mutatorPlaceholder . '->setValue(' . $collectionPlaceholder . ', ' . $valuePlaceholder . ')',
            $metadata
        );
    }
}
