<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\EmptyLine;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\LineListInterface;
use webignition\BasilCompilationSource\SourceInterface;

class LineListFactory
{
    public static function createForSourceLineList(
        SourceInterface $source,
        ?LineListInterface $teardownStatements = null
    ): LineList {
        return new LineList([
            new Comment('Code under test'),
            $source,
            new EmptyLine(),
            new Comment('Additional teardown statements'),
            $teardownStatements,
        ]);
    }
}
