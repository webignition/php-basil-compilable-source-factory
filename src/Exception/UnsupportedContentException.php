<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

class UnsupportedContentException extends \Exception
{
    public const TYPE_IDENTIFIER = 'identifier';
    public const TYPE_VALUE = 'value';

    private string $type;
    private ?string $content;

    public function __construct(string $type, ?string $content)
    {
        parent::__construct(sprintf('Unsupported content "%s": "%s"', $type, (string) $content));

        $this->content = $content;
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}
