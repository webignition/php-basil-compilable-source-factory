<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

use webignition\BasilModels\Step\StepInterface;

class UnsupportedStepException extends AbstractUnsupportedSubjectException
{
    public const CODE_UNSUPPORTED_ACTION = 2;
    public const CODE_UNSUPPORTED_ASSERTION = 3;

    private $step;

    public function __construct(StepInterface $step, \Throwable $previous)
    {
        parent::__construct($step, $previous);

        $this->step = $step;
    }

    public function getStep(): object
    {
        return $this->step;
    }

    /**
     * @inheritDoc
     */
    protected function createMessage($subject): string
    {
        return 'Unsupported step';
    }

    /**
     * @inheritDoc
     */
    protected function getCodes(): array
    {
        return [
            UnsupportedActionException::class => self::CODE_UNSUPPORTED_ACTION,
            UnsupportedAssertionException::class => self::CODE_UNSUPPORTED_ASSERTION,
        ];
    }
}
