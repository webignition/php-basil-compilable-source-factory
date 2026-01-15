<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Enum;

enum PhpUnitFailReason: string
{
    case INVALID_LOCATOR = 'locator-invalid';
    case ACTION_FAILED = 'action-failed';
    case ASSERTION_SETUP_FAILED = 'assertion-setup-failed';
    case ACTION_SETUP_FAILED = 'action-setup-failed';
}
