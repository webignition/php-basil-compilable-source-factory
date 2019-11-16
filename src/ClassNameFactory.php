<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModel\Test\TestInterface;

class ClassNameFactory
{
    public function create(TestInterface $test): string
    {
        $testName = $test->getName();
        $className = sprintf('Generated%sTest', ucfirst(md5($testName)));

        return $className;
    }
}
