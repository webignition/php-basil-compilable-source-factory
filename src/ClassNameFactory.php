<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilDataStructure\Test\Test;

class ClassNameFactory
{
    public function create(Test $test): string
    {
        $testName = $test->getPath();
        $className = sprintf('Generated%sTest', ucfirst(md5($testName)));

        return $className;
    }
}
