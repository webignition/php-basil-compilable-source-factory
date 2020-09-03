<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModels\Test\TestInterface;

class ClassNameFactory
{
    public function create(TestInterface $test): string
    {
        return sprintf('Generated%sTest', $this->createHash($test));
    }

    private function createHash(TestInterface $test): string
    {
        $configuration = $test->getConfiguration();

        $hashComponents = [
            'path' => $test->getPath(),
            'config' => [
                'browser' => $configuration->getBrowser(),
            ],
        ];

        return ucfirst(md5(json_encode($hashComponents)));
    }
}
