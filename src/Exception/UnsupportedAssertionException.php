<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

use webignition\BasilDataStructure\AssertionInterface;

class UnsupportedAssertionException extends \Exception
{
    private $assertion;

    public function __construct(AssertionInterface $assertion)
    {
        parent::__construct('Unsupported assertion "' . $assertion->getSource() . '"');

        $this->assertion = $assertion;
    }

    public function getAssertion(): object
    {
        return $this->assertion;
    }
}
