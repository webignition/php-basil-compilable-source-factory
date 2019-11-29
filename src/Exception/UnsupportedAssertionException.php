<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

use webignition\BasilModels\Assertion\AssertionInterface;

class UnsupportedAssertionException extends \Exception
{
    public const CODE_NONE = 0;
    public const CODE_UNKNOWN = 1;
    public const CODE_UNSUPPORTED_IDENTIFIER = 2;
    public const CODE_UNSUPPORTED_VALUE = 3;
    public const CODE_UNSUPPORTED_COMPARISON = 4;

    private $assertion;

    private $codes = [
        UnsupportedIdentifierException::class => self::CODE_UNSUPPORTED_IDENTIFIER,
        UnsupportedValueException::class => self::CODE_UNSUPPORTED_VALUE,
        UnsupportedComparisonException::class => self::CODE_UNSUPPORTED_COMPARISON,
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
