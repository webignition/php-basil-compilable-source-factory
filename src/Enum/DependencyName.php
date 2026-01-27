<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Enum;

enum DependencyName: string
{
    case PANTHER_CLIENT = 'CLIENT';
    case ENVIRONMENT_VARIABLE_ARRAY = 'ENV';
    case DOM_CRAWLER_NAVIGATOR = 'NAVIGATOR';
    case PHPUNIT_TEST_CASE = 'PHPUNIT';
    case WEBDRIVER_ELEMENT_INSPECTOR = 'INSPECTOR';
    case WEBDRIVER_ELEMENT_MUTATOR = 'MUTATOR';
    case MESSAGE_FACTORY = 'MESSAGE_FACTORY';
    case PANTHER_CRAWLER = 'CRAWLER';
}
