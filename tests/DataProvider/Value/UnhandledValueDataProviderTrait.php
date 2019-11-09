<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Value;

use webignition\BasilModel\Value\DomIdentifierReference;
use webignition\BasilModel\Value\DomIdentifierReferenceType;
use webignition\BasilModel\Value\PageElementReference;

trait UnhandledValueDataProviderTrait
{
    public function unhandledValueDataProvider(): array
    {
        return [
            'unhandled value: element parameter object' => [
                'model' => new DomIdentifierReference(DomIdentifierReferenceType::ELEMENT, '', ''),
            ],
            'unhandled value: page element reference' => [
                'model' => new PageElementReference('', '', ''),
            ],
            'unhandled value: malformed page property object' => [
                'model' => new PageElementReference(
                    '',
                    '',
                    ''
                ),
            ],
            'unhandled value: attribute parameter' => [
                'model' => new DomIdentifierReference(DomIdentifierReferenceType::ATTRIBUTE, '', ''),
            ],
        ];
    }
}
