<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Attribute;

use webignition\BaseBasilTestCase\Attribute\StepName;
use webignition\BasilCompilableSourceFactory\Model\ClassName;

class StepNameAttribute extends Attribute implements AttributeInterface
{
    public function __construct(string $stepName)
    {
        parent::__construct(new ClassName(StepName::class), ["'" . $stepName . "'"]);
    }
}
