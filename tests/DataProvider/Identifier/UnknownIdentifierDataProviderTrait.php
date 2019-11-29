<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Identifier;

use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;

trait UnknownIdentifierDataProviderTrait
{
    public function unknownIdentifierStringDataProvider(): array
    {
        return [
            'empty' => [
                'identifierString' => '',
            ],
            'element reference' => [
                'identifierString' => '$elements.element_name',
            ],
            'page element reference' => [
                'identifierString' => '$page_import_name.elements.element_name',
            ],
        ];
    }
}
