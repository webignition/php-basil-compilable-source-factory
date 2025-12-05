<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use webignition\Stubble\DeciderFactory;
use webignition\Stubble\UnresolvedVariableFinder;
use webignition\Stubble\VariableResolver;
use webignition\StubbleResolvable\ResolvableInterface;

abstract class AbstractResolvableTestCase extends \PHPUnit\Framework\TestCase
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
