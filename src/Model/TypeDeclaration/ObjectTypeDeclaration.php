<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\TypeDeclaration;

use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\ResolvableStringableTrait;

class ObjectTypeDeclaration implements \Stringable, TypeDeclarationInterface
{
    use ResolvableStringableTrait;

    private ClassName $type;
    private MetadataInterface $metadata;

    public function __construct(ClassName $type)
    {
        $this->type = $type;

        $this->metadata = Metadata::create(classNames: [$type->getClassName()]);
    }

    public function __toString(): string
    {
        return $this->type->renderClassName();
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }
}
