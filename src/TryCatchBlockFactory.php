<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;

class TryCatchBlockFactory
{
    public static function createFactory(): TryCatchBlockFactory
    {
        return new TryCatchBlockFactory();
    }

    public function createForThrowable(
        BodyContentCollection $tryContent,
        BodyContentCollection $catchContent
    ): TryCatchBlock {
        return new TryCatchBlock(
            new TryBlock(new Body($tryContent)),
            new CatchBlock(
                new ObjectTypeDeclarationCollection([
                    new ObjectTypeDeclaration(new ClassName(\Throwable::class))
                ]),
                new Body($catchContent)
            ),
        );
    }
}
