<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Enum;

enum Type: string
{
    case VOID = 'void';
    case STRING = 'string';
    case INTEGER = 'int';
    case OBJECT = 'object';
    case BOOLEAN = 'bool';
    case ARRAY = 'array';
    case NULL = 'null';
}
