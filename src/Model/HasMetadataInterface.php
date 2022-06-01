<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

interface HasMetadataInterface
{
    public function getMetadata(): MetadataInterface;
}
