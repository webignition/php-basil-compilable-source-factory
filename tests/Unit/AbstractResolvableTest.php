<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvableRenderer;
use webignition\StubbleResolvable\ResolvableInterface;

abstract class AbstractResolvableTest extends \PHPUnit\Framework\TestCase
{
    public function assertRenderResolvable(string $expectedString, ResolvableInterface $resolvable): void
    {
        self::assertSame(
            $expectedString,
            ResolvableRenderer::resolve($resolvable)
        );
    }
}
