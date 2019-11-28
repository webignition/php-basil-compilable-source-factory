<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

use webignition\BasilDataStructure\Step;

class UnsupportedStepException extends \Exception
{
    public const CODE_UNKNOWN = 0;
    public const CODE_UNSUPPORTED_ACTION = 1;
    public const CODE_UNSUPPORTED_ASSERTION = 2;

    private $step;

    private $codes = [
        UnsupportedActionException::class => self::CODE_UNSUPPORTED_ACTION,
        UnsupportedAssertionException::class => self::CODE_UNSUPPORTED_ASSERTION,
    ];

    public function __construct(Step $step, \Throwable $previous)
    {
        $code = $this->codes[get_class($previous)] ?? self::CODE_UNKNOWN;

        parent::__construct('Unsupported step"', $code, $previous);

        $this->step = $step;
    }

    public function getStep(): object
    {
        return $this->step;
    }
}
