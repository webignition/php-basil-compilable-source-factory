<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;

class TryCatchBlockFactory
{
    public static function createFactory(): TryCatchBlockFactory
    {
        return new TryCatchBlockFactory();
    }

    public function createForThrowable(BodyInterface $tryBody, BodyInterface $catchBody): TryCatchBlock
    {
        return new TryCatchBlock(
            new TryBlock($tryBody),
            new CatchBlock(
                new CatchExpression(
                    new ObjectTypeDeclarationCollection([
                        new ObjectTypeDeclaration(new ClassName(\Throwable::class))
                    ])
                ),
                $catchBody
            ),
        );
    }
}
