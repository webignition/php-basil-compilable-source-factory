<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

interface MethodInvocationInterface extends InvocableInterface
{
    public const string ERROR_SUPPRESSION_PREFIX = '@';

    public function setIsErrorSuppressed(bool $isErrorSuppressed): static;
}
