<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

use webignition\BasilModels\Action\ActionInterface;

class UnsupportedActionException extends \Exception
{
    public const CODE_NONE = 0;
    public const CODE_UNKNOWN = 1;
    public const CODE_UNSUPPORTED_IDENTIFIER = 2;
    public const CODE_UNSUPPORTED_VALUE = 3;

    private $action;

    private $codes = [
        UnsupportedIdentifierException::class => self::CODE_UNSUPPORTED_IDENTIFIER,
        UnsupportedValueException::class => self::CODE_UNSUPPORTED_VALUE,
    ];

    public function __construct(ActionInterface $action, \Throwable $previous = null)
    {
        $code = self::CODE_NONE;

        if ($previous instanceof \Throwable) {
            $code = $this->codes[get_class($previous)] ?? self::CODE_UNKNOWN;
        }

        parent::__construct(
            'Unsupported action "' . $action->getSource() . '"',
            $code,
            $previous
        );

        $this->action = $action;
    }

    public function getAction(): object
    {
        return $this->action;
    }
}
