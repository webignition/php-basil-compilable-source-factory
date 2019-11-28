<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

class UnsupportedValueException extends \Exception
{
    private $value;

    public function __construct(string $value)
    {
        parent::__construct('Unsupported value "' . $value . '"');

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
