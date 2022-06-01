<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\BasilCompilableSourceFactory\Model\EmptyLine;

class EmptyLineTest extends AbstractResolvableTest
{
    public function testRender(): void
    {
        $this->assertRenderResolvable('', new EmptyLine());
    }
}
