<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use webignition\Stubble\DeciderFactory;
use webignition\Stubble\Resolvable\ResolvableInterface;
use webignition\Stubble\UnresolvedVariableFinder;
use webignition\Stubble\VariableResolver;

abstract class AbstractResolvableTestCase extends TestCase
{
    public function assertRenderResolvable(string $expectedString, ResolvableInterface $resolvable): void
    {
        $resolver = new VariableResolver(
            new UnresolvedVariableFinder([
                DeciderFactory::createAllowAllDecider()
            ])
        );

        self::assertSame(
            $expectedString,
            $resolver->resolveAndIgnoreUnresolvedVariables($resolvable)
        );
    }
}
