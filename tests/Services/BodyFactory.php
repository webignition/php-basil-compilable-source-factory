<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;

class BodyFactory
{
    public static function createForSourceBlock(
        BodyInterface $source,
        ?BodyInterface $setupStatements,
        ?BodyInterface $teardownStatements = null
    ): Body {
        if (null === $setupStatements) {
            $setupStatements = new Body();
        }

        if (null === $teardownStatements) {
            $teardownStatements = new Body();
        }

        return new Body(
            new BodyContentCollection()
                ->append(
                    new SingleLineComment('Additional setup statements'),
                )
                ->append($setupStatements)
                ->append(new EmptyLine())
                ->append(
                    new SingleLineComment('Code under test'),
                )
                ->append(
                    $source,
                )
                ->append(
                    new EmptyLine(),
                )->append(
                    new SingleLineComment('Additional teardown statements'),
                )->append(
                    $teardownStatements,
                )
        );
    }
}
