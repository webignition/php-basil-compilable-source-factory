<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Enum;

enum VariableName: string
{
    case PANTHER_CLIENT = 'CLIENT';
    case ENVIRONMENT_VARIABLE_ARRAY = 'ENV';
    case DOM_CRAWLER_NAVIGATOR = 'NAVIGATOR';
    case PHPUNIT_TEST_CASE = 'PHPUNIT';
    case WEBDRIVER_ELEMENT_INSPECTOR = 'INSPECTOR';
    case WEBDRIVER_ELEMENT_MUTATOR = 'MUTATOR';
    case PANTHER_CRAWLER = 'CRAWLER';
    case ACTION_FACTORY = 'ACTION_FACTORY';
    case ASSERTION_FACTORY = 'ASSERTION_FACTORY';
}
