<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

use webignition\BasilDataStructure\Action\ActionInterface;

class UnsupportedActionException extends \Exception
{
    private const CODE_NONE = 0;
    private const CODE_UNKNOWN = 1;

    private $action;

    private $codes = [
        UnsupportedIdentifierException::class => 2,
        UnsupportedValueException::class => 3,
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
