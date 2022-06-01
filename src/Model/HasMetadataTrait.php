<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

trait HasMetadataTrait
{
    private MetadataInterface $metadata;

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }
}
