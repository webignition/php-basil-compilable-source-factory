<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Enum;

enum PhpUnitFailReason: string
{
    case INVALID_LOCATOR = 'locator-invalid';
    case ACTION_FAILED = 'action-failed';
}
