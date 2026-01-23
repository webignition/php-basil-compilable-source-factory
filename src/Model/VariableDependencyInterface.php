<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

interface VariableDependencyInterface extends HasMetadataInterface
{
    public function getName(): string;
}
