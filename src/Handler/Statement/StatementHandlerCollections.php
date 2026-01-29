<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;

class StatementHandlerCollections
{
    private ?BodyContentCollection $setup = null;
    private readonly BodyContentCollection $body;

    public function __construct(BodyContentCollection $body)
    {
        $this->body = $body;
    }

    public function getSetup(): ?BodyContentCollection
    {
        return $this->setup ?? null;
    }

    public function withSetup(?BodyContentCollection $setup): StatementHandlerCollections
    {
        $new = clone $this;
        $new->setup = $setup;

        return $new;
    }

    public function getBody(): BodyContentCollection
    {
        return $this->body;
    }
}
