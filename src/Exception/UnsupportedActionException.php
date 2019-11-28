<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

use webignition\BasilDataStructure\Action\ActionInterface;

class UnsupportedActionException extends \Exception
{
    private $action;

    public function __construct(ActionInterface $action)
    {
        parent::__construct('Unsupported action "' . $action->getSource() . '"');

        $this->action = $action;
    }

    public function getAction(): object
    {
        return $this->action;
    }
}
