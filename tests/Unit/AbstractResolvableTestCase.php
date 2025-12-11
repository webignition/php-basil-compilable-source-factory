<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvableRenderer;
use webignition\Stubble\Resolvable\ResolvableInterface;

abstract class AbstractResolvableTestCase extends TestCase
{
    public function assertRenderResolvable(string $expectedString, ResolvableInterface $resolvable): void
    {
        self::assertSame(
            $expectedString,
            ResolvableRenderer::resolve($resolvable)
        );
    }
}
