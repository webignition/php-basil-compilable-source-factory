<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\Stubble\Resolvable\ResolvableInterface;
use webignition\Stubble\Resolvable\ResolvableWithoutContext;

trait ResolvableStringableTrait
{
    use DeferredResolvableCreationTrait;

    protected function createResolvable(): ResolvableInterface
    {
        return new ResolvableWithoutContext((string) $this);
    }
}
