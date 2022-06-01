<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\TypeDeclaration;

use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\StubbleResolvable\ResolvableInterface;

interface TypeDeclarationCollectionInterface extends ResolvableInterface
{
    public function getMetadata(): MetadataInterface;
}
