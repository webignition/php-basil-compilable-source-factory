<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;

readonly class ArgumentFactory
{
    public function __construct(
        private SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {}

    public static function createFactory(): self
    {
        return new ArgumentFactory(
            SingleQuotedStringEscaper::create()
        );
    }

    public function create(string $argument): ExpressionInterface
    {
        return new LiteralExpression(sprintf(
            '\'%s\'',
            $this->singleQuotedStringEscaper->escape($argument)
        ));
    }
}
