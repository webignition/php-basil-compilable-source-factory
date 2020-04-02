<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilAssertionFailureMessage\AssertionFailureMessage;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\DerivedAssertionInterface;

class AssertionFailureMessageFactory
{
    public static function createFactory(): AssertionFailureMessageFactory
    {
        return new AssertionFailureMessageFactory();
    }

    public function createForAssertion(AssertionInterface $assertion): string
    {
        $derivationSource = null;
        if ($assertion instanceof DerivedAssertionInterface) {
            $derivationSource = $assertion->getSourceStatement();
        }

        return (string) json_encode(
            new AssertionFailureMessage($assertion, $derivationSource),
            JSON_PRETTY_PRINT
        );
    }
}
