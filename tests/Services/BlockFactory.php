<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;

class BlockFactory
{
    public static function createForSourceBlock(
        BlockInterface $source,
        ?BlockInterface $teardownStatements = null
    ): Block {
        return new Block([
            new Comment('Code under test'),
            $source,
            new EmptyLine(),
            new Comment('Additional teardown statements'),
            $teardownStatements,
        ]);
    }
}
