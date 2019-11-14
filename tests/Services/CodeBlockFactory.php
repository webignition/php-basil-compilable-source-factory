<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;

class CodeBlockFactory
{
    public static function createForSourceBlock(
        CodeBlockInterface $source,
        ?CodeBlockInterface $teardownStatements = null
    ): CodeBlock {
        return new CodeBlock([
            new Comment('Code under test'),
            $source,
            new EmptyLine(),
            new Comment('Additional teardown statements'),
            $teardownStatements,
        ]);
    }
}
