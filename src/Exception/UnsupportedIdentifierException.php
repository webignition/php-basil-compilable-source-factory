<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

class UnsupportedIdentifierException extends \Exception
{
    private $identifier;

    public function __construct(string $identifier)
    {
        parent::__construct('Unknown identifier "' . $identifier . '"');

        $this->identifier = $identifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
