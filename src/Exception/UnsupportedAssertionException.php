<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

use webignition\BasilDataStructure\AssertionInterface;

class UnsupportedAssertionException extends \Exception
{
    private const CODE_NONE = 0;
    private const CODE_UNKNOWN = 1;

    private $assertion;

    private $codes = [
        UnsupportedIdentifierException::class => 2,
        UnsupportedValueException::class => 3,
        UnsupportedComparisonException::class => 4,
    ];

    public function __construct(AssertionInterface $assertion, \Throwable $previous = null)
    {
        $code = self::CODE_NONE;

        if ($previous instanceof \Throwable) {
            $code = $this->codes[get_class($previous)] ?? self::CODE_UNKNOWN;
        }

        parent::__construct(
            'Unsupported assertion "' . $assertion->getSource() . '"',
            $code,
            $previous
        );

        $this->assertion = $assertion;
    }

    public function getAssertion(): object
    {
        return $this->assertion;
    }
}
