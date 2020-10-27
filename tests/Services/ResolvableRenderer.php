<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\Stubble\DeciderFactory;
use webignition\Stubble\UnresolvedVariableFinder;
use webignition\Stubble\VariableResolver;
use webignition\StubbleResolvable\ResolvableInterface;

class ResolvableRenderer
{
    public static function resolve(ResolvableInterface $resolvable): string
    {
        $resolver = new VariableResolver(
            new UnresolvedVariableFinder([
                DeciderFactory::createAllowAllDecider()
            ])
        );

        return $resolver->resolveAndIgnoreUnresolvedVariables($resolvable);
    }
}
