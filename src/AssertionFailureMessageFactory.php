<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModels\Assertion\AssertionInterface;

class AssertionFailureMessageFactory
{
    public static function createFactory(): AssertionFailureMessageFactory
    {
        return new AssertionFailureMessageFactory();
    }

    public function createForAssertion(AssertionInterface $assertion): string
    {
        return (string) json_encode(
            [
                'assertion' => $assertion,
            ],
            JSON_PRETTY_PRINT
        );
    }
}
