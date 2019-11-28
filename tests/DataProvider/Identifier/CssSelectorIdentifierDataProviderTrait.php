<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Identifier;

use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;

trait CssSelectorIdentifierDataProviderTrait
{
    public function cssSelectorIdentifierDataProvider(): array
    {
        return [
            'css id selector' => [
                'identifierString' => '$"#element-id"',
                'expectedIdentifier' => new DomIdentifier('#element-id'),
            ],
            'css class selector, position: null' => [
                'identifierString' => '$".listed-item"',
                'expectedIdentifier' => new DomIdentifier('.listed-item'),
            ],
            'css class selector; position: 1' => [
                'identifierString' => '$".listed-item":1',
                'expectedIdentifier' => new DomIdentifier('.listed-item', 1),
            ],
            'css class selector; position: 3' => [
                'identifierString' => '$".listed-item":3',
                'expectedIdentifier' => new DomIdentifier('.listed-item', 3),
            ],
            'css class selector; position: -1' => [
                'identifierString' => '$".listed-item":-1',
                'expectedIdentifier' => new DomIdentifier('.listed-item', -1),
            ],
            'css class selector; position: -3' => [
                'identifierString' => '$".listed-item":-3',
                'expectedIdentifier' => new DomIdentifier('.listed-item', -3),
            ],
            'css class selector; position: first' => [
                'identifierString' => '$".listed-item":first',
                'expectedIdentifier' => new DomIdentifier('.listed-item', 1),
            ],
            'css class selector; position: last' => [
                'identifierString' => '$".listed-item":last',
                'expectedIdentifier' => new DomIdentifier('.listed-item', -1),
            ],
        ];
    }
}