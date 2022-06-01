<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;

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
