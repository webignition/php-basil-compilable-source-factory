<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\EmptyLine;
use webignition\BasilCompilableSource\SingleLineComment;

class BodyFactory
{
    public static function createForSourceBlock(
        BodyInterface $source,
        ?BodyInterface $teardownStatements = null
    ): Body {
        if (null === $teardownStatements) {
            $teardownStatements = new Body([]);
        }

        return new Body([
            new SingleLineComment('Code under test'),
            $source,
            new EmptyLine(),
            new SingleLineComment('Additional teardown statements'),
            $teardownStatements,
        ]);
    }
}
