<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Identifier;

use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;

trait DescendantIdentifierDataProviderTrait
{
    public function descendantIdentifierDataProvider(): array
    {
        return [
            'css parent, xpath child' => [
                'identifierString' => '{{ $".parent" }} $"/child"',
                'expectedIdentifier' => (new DomIdentifier('/child'))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
            ],
            'xpath parent, css child' => [
                'identifierString' => '{{ $"/parent" }} $".child"',
                'expectedIdentifier' => (new DomIdentifier('.child'))
                    ->withParentIdentifier(new DomIdentifier('/parent')),
            ],
            'css parent, css child' => [
                'identifierString' => '{{ $".parent" }} $".child"',
                'expectedIdentifier' => (new DomIdentifier('.child'))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
            ],
            'xpath parent, xpath child' => [
                'identifierString' => '{{ $"/parent" }} $"/child"',
                'expectedIdentifier' => (new DomIdentifier('/child'))
                    ->withParentIdentifier(new DomIdentifier('/parent')),
            ],
        ];
    }
}
