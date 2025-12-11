<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Attribute;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\ClassName;

class DataProviderAttribute extends Attribute implements AttributeInterface
{
    public function __construct(string $dataProviderMethodName)
    {
        parent::__construct(new ClassName(DataProvider::class), ["'" . $dataProviderMethodName . "'"]);
    }
}
