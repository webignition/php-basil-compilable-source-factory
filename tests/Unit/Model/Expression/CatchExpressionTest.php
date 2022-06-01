<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTest;

class CatchExpressionTest extends AbstractResolvableTest
{
    public function testGetMetadata(): void
    {
        $typeDeclarationCollection = new ObjectTypeDeclarationCollection([
            new ObjectTypeDeclaration(new ClassName(\LogicException::class)),
            new ObjectTypeDeclaration(new ClassName(\RuntimeException::class)),
        ]);

        $expression = new CatchExpression($typeDeclarationCollection);

        $expectedMetadata = new Metadata([
            Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                new ClassName(\LogicException::class),
                new ClassName(\RuntimeException::class),
            ]),
        ]);

        $this->assertEquals($expectedMetadata, $expression->getMetadata());
    }

    public function testRender(): void
    {
        $typeDeclarationCollection = new ObjectTypeDeclarationCollection([
            new ObjectTypeDeclaration(new ClassName(\LogicException::class)),
            new ObjectTypeDeclaration(new ClassName(\RuntimeException::class)),
        ]);

        $expression = new CatchExpression($typeDeclarationCollection);

        $this->assertRenderResolvable('\LogicException | \RuntimeException $exception', $expression);
    }
}
