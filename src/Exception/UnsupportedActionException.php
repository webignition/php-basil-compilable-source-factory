<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

use webignition\BasilModels\Action\ActionInterface;

class UnsupportedActionException extends AbstractUnsupportedSubjectException
{
    public const CODE_UNSUPPORTED_IDENTIFIER = 2;
    public const CODE_UNSUPPORTED_VALUE = 3;

    private $action;

    public function __construct(ActionInterface $action, \Throwable $previous = null)
    {
        parent::__construct($action, $previous);

        $this->action = $action;
    }

    public function getAction(): ActionInterface
    {
        return $this->action;
    }

    /**
     * @inheritDoc
     */
    protected function createMessage($subject): string
    {
        return $subject instanceof ActionInterface ? 'Unsupported action "' . $subject->getSource() . '"' : '';
    }

    /**
     * @return array<string, int>
     */
    protected function getCodes(): array
    {
        return [
            UnsupportedIdentifierException::class => self::CODE_UNSUPPORTED_IDENTIFIER,
            UnsupportedValueException::class => self::CODE_UNSUPPORTED_VALUE,
        ];
    }
}
