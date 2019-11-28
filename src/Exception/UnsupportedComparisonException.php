<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

class UnsupportedComparisonException extends \Exception
{
    private $comparison;

    public function __construct(?string $comparison)
    {
        parent::__construct('Unknown comparison "' . $comparison . '"');

        $this->comparison = $comparison;
    }

    public function getComparison(): ?string
    {
        return $this->comparison;
    }
}
