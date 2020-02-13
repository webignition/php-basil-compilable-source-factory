<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\VariableNames;

class WebDriverElementMutatorCallFactory
{
    public static function createFactory(): WebDriverElementMutatorCallFactory
    {
        return new WebDriverElementMutatorCallFactory();
    }

    public function createSetValueCall(
        VariablePlaceholder $collectionPlaceholder,
        VariablePlaceholder $valuePlaceholder
    ): ExpressionInterface {
        return new ObjectMethodInvocation(
            VariablePlaceholder::createDependency(VariableNames::WEBDRIVER_ELEMENT_MUTATOR),
            'setValue',
            [
                $collectionPlaceholder,
                $valuePlaceholder
            ]
        );
    }
}
