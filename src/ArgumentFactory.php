<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;

class ArgumentFactory
{
    public function __construct(
        private readonly SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {}

    public static function createFactory(): self
    {
        return new ArgumentFactory(
            SingleQuotedStringEscaper::create()
        );
    }

    public function create(mixed $argument): ExpressionInterface
    {
        if (is_bool($argument)) {
            return new LiteralExpression($argument ? 'true' : 'false');
        }

        if (is_float($argument) || is_int($argument)) {
            return new LiteralExpression((string) $argument);
        }

        if (is_string($argument)) {
            return new LiteralExpression(sprintf(
                '\'%s\'',
                $this->singleQuotedStringEscaper->escape($argument)
            ));
        }

        return new LiteralExpression('null');
    }
}
