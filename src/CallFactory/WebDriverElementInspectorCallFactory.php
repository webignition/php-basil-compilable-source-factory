<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSource\VariablePlaceholder;
use webignition\BasilCompilableSourceFactory\VariableNames;

class WebDriverElementInspectorCallFactory
{
    public static function createFactory(): WebDriverElementInspectorCallFactory
    {
        return new WebDriverElementInspectorCallFactory();
    }

    public function createGetValueCall(VariablePlaceholder $collectionPlaceholder): ExpressionInterface
    {
        return new ObjectMethodInvocation(
            VariablePlaceholder::createDependency(VariableNames::WEBDRIVER_ELEMENT_INSPECTOR),
            'getValue',
            [
                $collectionPlaceholder,
            ]
        );
    }
}
