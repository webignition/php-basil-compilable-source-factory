<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;

class DataProviderAttribute extends Attribute implements AttributeInterface
{
    public function __construct(string $stepName)
    {
        parent::__construct(
            new ClassName(DataProvider::class),
            new MethodArguments([
                LiteralExpression::string("'" . $stepName . "'"),
            ])
        );
    }
}
