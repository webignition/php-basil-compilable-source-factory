<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;

class StatementHandlerComponents
{
    private ?BodyInterface $setup = null;
    private readonly BodyInterface $body;

    public function __construct(BodyInterface $body)
    {
        $this->body = $body;
    }

    public function getSetup(): ?BodyInterface
    {
        return $this->setup ?? null;
    }

    public function withSetup(?BodyInterface $setup): StatementHandlerComponents
    {
        $new = clone $this;
        $new->setup = $setup;

        return $new;
    }

    public function getBody(): BodyInterface
    {
        return $this->body;
    }
}
