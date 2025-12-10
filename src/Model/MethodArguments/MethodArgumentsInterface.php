<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodArguments;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\HasMetadataInterface;
use webignition\StubbleResolvable\ResolvableInterface;

interface MethodArgumentsInterface extends HasMetadataInterface, ResolvableInterface
{
    public const string FORMAT_INLINE = 'inline';
    public const string FORMAT_STACKED = 'stacked';

    /**
     * @return ExpressionInterface[]
     */
    public function getArguments(): array;

    public function getFormat(): string;

    public function withArgument(ExpressionInterface $expression): MethodArgumentsInterface;

    public function withFormat(string $format): MethodArgumentsInterface;
}
