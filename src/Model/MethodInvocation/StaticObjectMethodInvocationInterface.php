<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\StaticObject;

interface StaticObjectMethodInvocationInterface extends MethodInvocationInterface
{
    public function getStaticObject(): StaticObject;
}
