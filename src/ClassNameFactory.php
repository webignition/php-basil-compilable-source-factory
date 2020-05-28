<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModels\Test\TestInterface;

class ClassNameFactory
{
    public function create(TestInterface $test): string
    {
        $testName = $test->getPath();
        return sprintf('Generated%sTest', ucfirst(md5($testName)));
    }
}
