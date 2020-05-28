<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\EmptyLine;
use webignition\BasilCompilableSource\Line\SingleLineComment;

class CodeBlockFactory
{
    public static function createForSourceBlock(
        CodeBlockInterface $source,
        ?CodeBlockInterface $teardownStatements = null
    ): CodeBlock {
        if (null === $teardownStatements) {
            $teardownStatements = new CodeBlock();
        }

        return new CodeBlock([
            new SingleLineComment('Code under test'),
            $source,
            new EmptyLine(),
            new SingleLineComment('Additional teardown statements'),
            $teardownStatements,
        ]);
    }
}
