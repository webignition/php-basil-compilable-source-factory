<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Step\StepInterface;

class UnsupportedStepException extends \Exception
{
    public const CODE_UNSUPPORTED_ACTION = 1;
    public const CODE_UNSUPPORTED_ASSERTION = 2;

    private $step;
    private $unsupportedStatementException;

    public function __construct(StepInterface $step, UnsupportedStatementException $unsupportedStatementException)
    {
        $code = $unsupportedStatementException->getStatement() instanceof ActionInterface
            ? self::CODE_UNSUPPORTED_ACTION
            : self::CODE_UNSUPPORTED_ASSERTION;

        parent::__construct('Unsupported step', $code, $unsupportedStatementException);

        $this->step = $step;
        $this->unsupportedStatementException = $unsupportedStatementException;
    }

    public function getStep(): StepInterface
    {
        return $this->step;
    }

    public function getUnsupportedStatementException(): UnsupportedStatementException
    {
        return $this->unsupportedStatementException;
    }
}
