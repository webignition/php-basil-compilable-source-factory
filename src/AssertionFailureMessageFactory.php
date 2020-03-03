<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\DerivedElementExistsAssertion;

class AssertionFailureMessageFactory
{
    public static function createFactory(): AssertionFailureMessageFactory
    {
        return new AssertionFailureMessageFactory();
    }

    public function createForAssertion(AssertionInterface $assertion): string
    {
        $data = [
            'assertion' => $assertion,
        ];

        if ($assertion instanceof DerivedElementExistsAssertion) {
            $sourceStatement = $assertion->getSourceStatement();

            $data['derived_from'] = [
                'statement_type' => $sourceStatement instanceof ActionInterface ? 'action' : 'assertion',
                'statement' => $sourceStatement,
            ];
        }

        return (string) json_encode($data, JSON_PRETTY_PRINT);
    }
}
