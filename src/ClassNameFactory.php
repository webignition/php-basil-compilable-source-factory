<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModels\Model\Test\NamedTestInterface;

class ClassNameFactory
{
    public function create(NamedTestInterface $test): string
    {
        return sprintf('Generated%sTest', ucfirst($this->createHash($test)));
    }

    private function createHash(NamedTestInterface $test): string
    {
        $hashComponents = [
            'path' => $test->getName(),
            'config' => [
                'browser' => $test->getBrowser(),
            ],
        ];

        return md5((string) json_encode($hashComponents));
    }
}
