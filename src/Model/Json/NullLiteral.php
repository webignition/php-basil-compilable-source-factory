<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Json;

readonly class NullLiteral extends UnquotedLiteral
{
    public function __construct()
    {
        parent::__construct('\'null\'');
    }
}
